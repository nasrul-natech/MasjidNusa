<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\DonationCategory;
use App\Models\PaymentConfiguration;
use App\Models\PaymentProof;
use App\Models\PaymentTransaction;
use App\Services\DuitkuService;
use App\Services\NotificationService;
use App\Support\PaymentProofStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DonationUserController extends Controller
{
    public function __construct(
        protected DuitkuService $duitkuService,
        protected NotificationService $notificationService
    ) {}

    public function quickDonate(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1000'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:500'],
            'is_anonymous' => ['boolean'],
            'category' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:manual,duitku'],
        ]);

        if ($response = $this->rejectDisabledMethod($data['payment_method'])) {
            return $response;
        }

        $user = auth()->user();
        $campaign = null;

        if (!empty($data['category'])) {
            $cat = DonationCategory::where('slug', $data['category'])->first();
            if ($cat) {
                $campaign = DonationCampaign::where('category_id', $cat->id)->active()->first();
            }
        }

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada program donasi aktif untuk kategori ini.',
            ], 422);
        }

        $isAnonymous = $data['is_anonymous'] ?? false;
        $donation = Donation::create([
            'donation_code' => 'DON-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'amount' => $data['amount'],
            'donor_name' => $isAnonymous ? 'Hamba Allah' : ($data['donor_name'] ?? $user->name),
            'donor_email' => $user->email,
            'donor_phone' => $user->phone ?? null,
            'message' => $data['message'] ?? null,
            'is_anonymous' => $isAnonymous,
            'status' => Donation::STATUS_PENDING,
            'payment_method_type' => $data['payment_method'],
        ]);

        return $this->startPayment($donation, $user, $data['payment_method']);
    }

    public function store(Request $request, string $slug)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:10000'],
            'message' => ['nullable', 'string', 'max:500'],
            'is_anonymous' => ['boolean'],
            'payment_method' => ['required', 'in:manual,duitku'],
        ]);

        $campaign = DonationCampaign::where('slug', $slug)->active()->firstOrFail();
        $user = auth()->user();

        if ($response = $this->rejectDisabledMethod($data['payment_method'])) {
            return $response;
        }

        $donation = Donation::create([
            'donation_code' => 'DON-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'amount' => $data['amount'],
            'donor_name' => ($data['is_anonymous'] ?? false) ? 'Hamba Allah' : $user->name,
            'donor_email' => $user->email,
            'donor_phone' => $user->phone,
            'message' => $data['message'],
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'status' => Donation::STATUS_PENDING,
            'payment_method_type' => $data['payment_method'],
        ]);

        return $this->startPayment($donation, $user, $data['payment_method']);
    }

    public function history()
    {
        $donations = Donation::where('user_id', auth()->id())
            ->with(['campaign', 'latestTransaction'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('user.donation-history', compact('donations'));
    }

    public function uploadProof(Request $request, int $transactionId)
    {
        $request->validate([
            'proof' => ['required', 'image', 'max:5120'],
        ]);

        $transaction = PaymentTransaction::with('donation')->findOrFail($transactionId);
        $donation = $transaction->donation;

        if (!$donation || (int) $donation->user_id !== (int) auth()->id()) {
            abort(403);
        }

        if ($donation->payment_method_type !== 'manual') {
            return back()->with('error', 'Bukti transfer hanya untuk pembayaran manual.');
        }

        $path = $request->file('proof')->store('payment-proofs', 'local');

        try {
            $saved = DB::transaction(function () use ($transaction, $donation, $path) {
                $lockedDonation = Donation::query()->whereKey($donation->id)->lockForUpdate()->first();
                $lockedTransaction = PaymentTransaction::query()->whereKey($transaction->id)->lockForUpdate()->first();

                if (
                    !$lockedDonation
                    || !$lockedTransaction
                    || (int) $lockedTransaction->donation_id !== (int) $lockedDonation->id
                    || $lockedDonation->payment_method_type !== 'manual'
                    || !in_array($lockedDonation->status, [Donation::STATUS_PENDING, Donation::STATUS_WAITING_VERIFICATION], true)
                ) {
                    return false;
                }

                $existing = $lockedTransaction->proof;
                if ($existing?->image && $existing->image !== $path) {
                    Storage::disk('local')->delete($existing->image);
                    Storage::disk('public')->delete($existing->image);
                }

                $lockedTransaction->proof()->updateOrCreate(
                    ['transaction_id' => $lockedTransaction->id],
                    [
                        'image' => $path,
                        'uploaded_at' => now(),
                    ]
                );

                if ($lockedDonation->status === Donation::STATUS_PENDING) {
                    $lockedDonation->update(['status' => Donation::STATUS_WAITING_VERIFICATION]);
                }

                return true;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        if (!$saved) {
            Storage::disk('local')->delete($path);

            return back()->with('error', 'Bukti tidak bisa diunggah untuk donasi yang sudah selesai.');
        }

        return redirect()->route('user.donations.history')->with('success', 'Bukti transfer berhasil diupload. Menunggu verifikasi admin.');
    }

    public function showProof(PaymentProof $proof)
    {
        $donation = $proof->transaction?->donation;
        $user = auth()->user();

        if (!$donation || !$proof->image || !$user || !$this->canViewProof($user, $donation)) {
            abort(403);
        }

        $path = $proof->image;
        PaymentProofStorage::migrateToPrivate($path);

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    }

    private function canViewProof($user, Donation $donation): bool
    {
        if ((int) $donation->user_id === (int) $user->id) {
            return true;
        }

        return $user->can('verify_payments') && (bool) request()->session()->get('two_factor_verified');
    }

    private function rejectDisabledMethod(string $method): ?JsonResponse
    {
        if ($method === 'manual' && !PaymentConfiguration::isManualEnabled()) {
            return response()->json(['success' => false, 'message' => 'Transfer manual tidak aktif.'], 422);
        }

        if ($method === 'duitku' && !PaymentConfiguration::isDuitkuEnabled()) {
            return response()->json(['success' => false, 'message' => 'Duitku tidak aktif.'], 422);
        }

        return null;
    }

    private function startPayment(Donation $donation, $user, string $method): JsonResponse
    {
        $paymentUrl = null;
        $transaction = null;

        if ($method === 'duitku') {
            $result = $this->duitkuService->createTransaction($donation, $user);
            if (!$result['success']) {
                $donation->delete();

                return response()->json(['success' => false, 'message' => $result['message']], 422);
            }

            $paymentUrl = $result['payment_url'];
        } else {
            $donation->update(['status' => Donation::STATUS_WAITING_VERIFICATION]);
            $transaction = $donation->transactions()->create([
                'amount' => $donation->amount,
                'reference_number' => 'TXN-' . strtoupper(Str::random(10)),
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'success' => true,
            'donation_id' => $donation->id,
            'donation_code' => $donation->donation_code,
            'payment_url' => $paymentUrl,
            'transaction_id' => $transaction->id ?? null,
        ]);
    }
}
