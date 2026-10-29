<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\DuitkuConfig;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DuitkuService
{
    protected ?DuitkuConfig $config;

    public function __construct()
    {
        $this->config = DuitkuConfig::active();
    }

    public function createTransaction(Donation $donation, User $user): array
    {
        if (!$this->config) {
            return ['success' => false, 'message' => 'Duitku belum dikonfigurasi.'];
        }

        $merchantCode = $this->config->merchant_code;
        $apiKey = $this->config->api_key;
        $merchantOrderId = $donation->donation_code;
        $paymentAmount = $donation->amount;
        $productDetails = 'Donasi: ' . ($donation->campaign?->title ?? 'Umum');
        $email = $user->email;
        $expiryPeriod = $this->config->expiry_period;
        $signature = md5($merchantCode . $apiKey . $merchantOrderId . $paymentAmount);
        $callbackUrl = $this->config->callback_url ?? route('webhook.duitku.callback');
        $returnUrl = $this->config->return_url ?? route('webhook.duitku.return');

        $baseUrl = $this->config->environment === 'production'
            ? 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry'
            : 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';

        try {
            $response = Http::timeout(30)->post($baseUrl, [
                'merchantCode' => $merchantCode,
                'paymentAmount' => $paymentAmount,
                'merchantOrderId' => $merchantOrderId,
                'productDetails' => $productDetails,
                'email' => $email,
                'callbackUrl' => $callbackUrl,
                'returnUrl' => $returnUrl,
                'expiryPeriod' => $expiryPeriod,
                'signature' => $signature,
                'paymentMethod' => '', // Allow all methods
            ]);

            $data = $response->json();

            if (isset($data['paymentUrl'])) {
                $method = PaymentMethod::where('code', 'duitku')->first();

                PaymentTransaction::create([
                    'donation_id' => $donation->id,
                    'payment_method_id' => $method?->id,
                    'amount' => $donation->amount,
                    'reference_number' => $data['reference'] ?? Str::random(20),
                    'status' => 'pending',
                    'gateway_response' => $data,
                    'payment_url' => $data['paymentUrl'],
                    'expired_at' => now()->addMinutes($expiryPeriod),
                ]);

                return ['success' => true, 'payment_url' => $data['paymentUrl']];
            }

            Log::error('Duitku error response', $data);
            return ['success' => false, 'message' => $data['Message'] ?? 'Gagal membuat transaksi Duitku.'];

        } catch (\Exception $e) {
            Log::error('Duitku exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Koneksi ke Duitku gagal.'];
        }
    }

    public function verifyCallback(array $data): bool
    {
        if (!$this->config) return false;

        $signature = md5(
            $this->config->merchant_code .
            $data['amount'] .
            $data['merchantOrderId'] .
            $this->config->api_key
        );

        return $signature === ($data['signature'] ?? '');
    }

    /**
     * Transaksi lokal yang cocok dengan callback bertanda tangan sah.
     * Nominal, merchant, referensi, dan metode harus sama. Kalau tidak, null.
     */
    public function matchingTransaction(Donation $donation, array $data): ?PaymentTransaction
    {
        if (!$this->config || $donation->payment_method_type !== 'duitku') {
            return null;
        }

        if ((string) ($data['merchantCode'] ?? '') !== (string) $this->config->merchant_code) {
            return null;
        }

        if (!$this->amountsMatch((int) $donation->amount, $data['amount'] ?? null)) {
            return null;
        }

        $reference = trim((string) ($data['reference'] ?? ''));
        if ($reference === '') {
            return null;
        }

        return $donation->transactions()->where('reference_number', $reference)->first();
    }

    private function amountsMatch(int $expected, mixed $payload): bool
    {
        if (!is_numeric($payload)) {
            return false;
        }

        return (int) round((float) $payload) === $expected;
    }
}
