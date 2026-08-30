<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_donations' => Donation::whereIn('status', ['verified', 'paid'])->sum('amount'),
            'total_count' => Donation::count(),
            'pending_verification' => Donation::where('status', 'waiting_verification')->count(),
            'this_month' => Donation::whereIn('status', ['verified', 'paid'])->whereMonth('created_at', now()->month)->sum('amount'),
        ];

        $recentDonations = Donation::with(['user', 'campaign'])->orderByDesc('created_at')->limit(10)->get();

        $monthlyChart = Donation::whereIn('status', ['verified', 'paid'])
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $label = now()->subMonths($i)->translatedFormat('M Y');
            $row = $monthlyChart->firstWhere('month', $key);
            $months->push([
                'label' => $label,
                'total' => (float) ($row->total ?? 0),
                'count' => (int) ($row->count ?? 0),
            ]);
        }

        $statusBreakdown = Donation::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.keuangan.dashboard', compact('stats', 'recentDonations', 'months', 'statusBreakdown'));
    }
}
