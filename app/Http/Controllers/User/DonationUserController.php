<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\DonationCategory;
use App\Models\PaymentConfiguration;
use App\Models\PaymentTransaction;
use App\Services\DuitkuService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
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

        $user = auth()->user();

        $campaign = null;
        if (!empty($data['category'])) {
            $cat = DonationCategory::where('slug', $data['category'])->first();
            if ($cat) {
                $campaign = DonationCampaign::where('category_id', $cat->id)->active()->first();
            }
        }
        if (!$campaign) {
            $campaign = DonationCampaign::active()->first();
        }

        $donation = Donation::create([
            'donation_code' => 'DON-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'campaign_id' => $campaign?->id,
            'amount' => $data['amount'],
            'donor_name' => ($data['is_anonymous'] ?? false) ? 'Hamba Allah' : ($data['donor_name'] ?? $user->name),
            'donor_email' => $user->email,
            'donor_phone' => $user->phone ?? null,
            'message' => $data['message'],
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'status' => Donation::STATUS_WAITING_VERIFICATION,
            'payment_method_type' => $data['payment_method'],
        ]);

        $transaction = $donation->transactions()->create([
            'amount' => $donation->amount,
            'reference_number' => 'TXN-' . strtoupper(Str::random(10)),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'donation_id' => $donation->id,
            'donation_code' => $donation->donation_code,
            'transaction_id' => $transaction->id,
        ]);
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

        // Validate payment method is enabled
        if ($data['payment_method'] === 'manual' && !PaymentConfiguration::isManualEnabled()) {
            return response()->json(['success' => false, 'message' => 'Transfer manual tidak aktif.'], 422);
        }
        if ($data['payment_method'] === 'duitku' && !PaymentConfiguration::isDuitkuEnabled()) {
            return response()->json(['success' => false, 'message' => 'Duitku tidak aktif.'], 422);
        }

        $donation = Donation::create([
            'donation_code' => 'DON-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'amount' => $data['amount'],
            'donor_name' => $data['is_anonymous'] ? 'Hamba Allah' : $user->name,
            'donor_email' => $user->email,
            'donor_phone' => $user->phone,
            'message' => $data['message'],
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'status' => Donation::STATUS_PENDING,
            'payment_method_type' => $data['payment_method'],
        ]);

        $paymentUrl = null;
        $transaction = null;

        if ($data['payment_method'] === 'duitku') {
            $result = $this->duitkuService->createTransaction($donation, $user);
            if ($result['success']) {
                $paymentUrl = $result['payment_url'];
                $donation->update(['status' => Donation::STATUS_PENDING]);
            } else {
                $donation->delete();
                return response()->json(['success' => false, 'message' => $result['message']], 422);
            }
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

        $transaction = PaymentTransaction::findOrFail($transactionId);

        // Ensure the donation belongs to the user
        if ($transaction->donation->user_id !== auth()->id()) {
            abort(403);
        }

        $path = $request->file('proof')->store('payment-proofs', 'public');

        $transaction->proof()->updateOrCreate(
            ['transaction_id' => $transaction->id],
            [
                'image' => $path,
                'uploaded_at' => now(),
            ]
        );

        $transaction->donation->update(['status' => Donation::STATUS_WAITING_VERIFICATION]);

        return redirect()->route('user.donations.history')->with('success', 'Bukti transfer berhasil diupload. Menunggu verifikasi admin.');
    }
}
