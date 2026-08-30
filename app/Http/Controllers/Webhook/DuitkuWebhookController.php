<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\PaymentTransaction;
use App\Services\DuitkuService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DuitkuWebhookController extends Controller
{
    public function __construct(
        protected DuitkuService $duitkuService,
        protected NotificationService $notificationService
    ) {}

    public function callback(Request $request)
    {
        Log::info('Duitku callback received', $request->all());

        $data = $request->all();

        if (!$this->duitkuService->verifyCallback($data)) {
            Log::warning('Duitku invalid signature');
            return response('Invalid signature', 400);
        }

        $merchantOrderId = $data['merchantOrderId'] ?? null;
        $resultCode = $data['resultCode'] ?? null;

        $donation = Donation::where('donation_code', $merchantOrderId)->first();

        if (!$donation) {
            Log::warning('Duitku: Donation not found for order ' . $merchantOrderId);
            return response('Not found', 404);
        }

        $transaction = $donation->latestTransaction;

        if ($resultCode === '00') {
            // Payment success
            $donation->update(['status' => Donation::STATUS_PAID]);
            if ($transaction) {
                $transaction->update([
                    'status' => 'paid',
                    'gateway_response' => array_merge($transaction->gateway_response ?? [], $data),
                    'paid_at' => now(),
                ]);
            }

            // Update campaign collected amount
            if ($donation->campaign) {
                $donation->campaign->increment('collected_amount', $donation->amount);
            }

            // Send notifications
            $this->notificationService->sendPaymentSuccess($donation);
        } else {
            $donation->update(['status' => Donation::STATUS_FAILED]);
            if ($transaction) {
                $transaction->update(['status' => 'failed', 'gateway_response' => $data]);
            }
        }

        return response('OK', 200);
    }

    public function return(Request $request)
    {
        $merchantOrderId = $request->get('merchantOrderId');
        $donation = Donation::where('donation_code', $merchantOrderId)->first();

        if ($donation && in_array($donation->status, [Donation::STATUS_PAID, Donation::STATUS_VERIFIED])) {
            return redirect()->route('user.donations.history')
                ->with('success', 'Pembayaran berhasil! Jazakallah khairan.');
        }

        return redirect()->route('user.donations.history')
            ->with('warning', 'Status pembayaran sedang diproses.');
    }
}
