<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function donations(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        $donations = Donation::with(['user', 'campaign'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'total' => Donation::whereIn('status', ['verified', 'paid'])->whereYear('created_at', $year)->whereMonth('created_at', $monthNum)->sum('amount'),
            'count' => Donation::whereYear('created_at', $year)->whereMonth('created_at', $monthNum)->count(),
            'verified' => Donation::whereIn('status', ['verified', 'paid'])->whereYear('created_at', $year)->whereMonth('created_at', $monthNum)->count(),
        ];

        return view('admin.keuangan.reports.donations', compact('donations', 'stats', 'month'));
    }

    public function payments(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        $transactions = PaymentTransaction::with(['donation.user', 'paymentMethod'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.keuangan.reports.payments', compact('transactions', 'month'));
    }

    public function export(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        $donations = Donation::with(['user', 'campaign'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $monthNum)
            ->whereIn('status', ['verified', 'paid'])
            ->get();

        $filename = "laporan-donasi-{$month}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($donations) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel
            fputcsv($file, ['Kode Donasi', 'Nama Donatur', 'Email', 'Kampanye', 'Jumlah', 'Status', 'Tanggal']);

            foreach ($donations as $donation) {
                fputcsv($file, [
                    $this->csvText($donation->donation_code),
                    $this->csvText($donation->donor_name),
                    $this->csvText($donation->donor_email),
                    $this->csvText($donation->campaign?->title ?? '-'),
                    $donation->amount,
                    $this->csvText($donation->status),
                    $this->csvText($donation->created_at->format('d/m/Y H:i')),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function csvText(mixed $value): string
    {
        $text = (string) ($value ?? '');

        if ($text !== '' && in_array($text[0], ['=', '+', '-', '@'], true)) {
            return "'".$text;
        }

        return $text;
    }
}
