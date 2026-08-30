<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Donation;
use App\Models\PaymentProof;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PaymentVerificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index()
    {
        $pendingDonations = Donation::where('status', 'waiting_verification')
            ->with(['user', 'campaign', 'latestTransaction.proof'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.keuangan.verify-payment.index', compact('pendingDonations'));
    }

    public function approve(int $id)
    {
        $donation = Donation::findOrFail($id);
        $donation->update(['status' => Donation::STATUS_VERIFIED]);

        if ($donation->latestTransaction) {
            $donation->latestTransaction->update(['status' => 'paid', 'paid_at' => now()]);

            if ($donation->latestTransaction->proof) {
                $donation->latestTransaction->proof->update([
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                ]);
            }
        }

        // Update campaign
        if ($donation->campaign) {
            $donation->campaign->increment('collected_amount', $donation->amount);
        }

        $this->notificationService->sendPaymentSuccess($donation);
        AuditLog::record('payment.verified', $donation);

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Request $request, int $id)
    {
        $donation = Donation::findOrFail($id);
        $donation->update(['status' => Donation::STATUS_REJECTED]);

        if ($donation->latestTransaction) {
            $donation->latestTransaction->update(['status' => 'failed']);
        }

        AuditLog::record('payment.rejected', $donation);

        return back()->with('success', 'Pembayaran ditolak.');
    }
}
