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

        if ($donation->isSettled()) {
            return back()->with('success', 'Pembayaran sudah tercatat.');
        }

        if ($donation->status !== Donation::STATUS_WAITING_VERIFICATION) {
            return back()->with('error', 'Hanya donasi yang menunggu verifikasi yang bisa disetujui.');
        }

        $verifierId = auth()->id();
        $settled = $donation->settle(Donation::STATUS_VERIFIED, [Donation::STATUS_WAITING_VERIFICATION], function (Donation $locked) use ($verifierId) {
            $transaction = $locked->latestTransaction()->first();
            if (!$transaction) {
                return;
            }

            $transaction->update(['status' => 'paid', 'paid_at' => now()]);

            if ($transaction->proof) {
                $transaction->proof->update([
                    'verified_at' => now(),
                    'verified_by' => $verifierId,
                ]);
            }
        });

        if (!$settled) {
            return back()->with('success', 'Pembayaran sudah tercatat.');
        }

        $donation->refresh();
        $this->notificationService->sendPaymentSuccess($donation);
        AuditLog::record('payment.verified', $donation);

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(Request $request, int $id)
    {
        $donation = Donation::findOrFail($id);

        if ($donation->isSettled()) {
            return back()->with('error', 'Pembayaran yang sudah selesai tidak bisa ditolak.');
        }

        $closed = $donation->closeIfOpen(Donation::STATUS_REJECTED, [Donation::STATUS_PENDING, Donation::STATUS_WAITING_VERIFICATION], function (Donation $locked) {
            $transaction = $locked->latestTransaction()->first();
            if ($transaction) {
                $transaction->update(['status' => 'failed']);
            }
        });

        if ($closed) {
            AuditLog::record('payment.rejected', $donation->fresh());
        }

        return back()->with('success', 'Pembayaran ditolak.');
    }
}
