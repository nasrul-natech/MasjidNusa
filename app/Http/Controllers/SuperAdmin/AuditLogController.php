<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($request->user_id) $query->where('user_id', $request->user_id);
        if ($request->action) $query->where('action', 'like', "%{$request->action}%");
        if ($request->date) $query->whereDate('created_at', $request->date);

        $logs = $query->paginate(30);

        $stats = [
            'total'   => AuditLog::count(),
            'oldest'  => AuditLog::oldest('created_at')->value('created_at'),
            'month_1' => AuditLog::where('created_at', '<', now()->subMonth())->count(),
            'month_6' => AuditLog::where('created_at', '<', now()->subMonths(6))->count(),
            'year_1'  => AuditLog::where('created_at', '<', now()->subYear())->count(),
        ];

        return view('super-admin.audit-log.index', compact('logs', 'stats'));
    }

    public function purgeLogs(Request $request)
    {
        $period = $request->input('period');

        $cutoff = match($period) {
            '1_month' => now()->subMonth(),
            '6_months' => now()->subMonths(6),
            '1_year' => now()->subYear(),
            default => null,
        };

        if (!$cutoff) {
            return back()->with('error', 'Periode penghapusan tidak valid.');
        }

        $deleted = AuditLog::where('created_at', '<', $cutoff)->delete();

        $labels = [
            '1_month'  => '1 bulan terakhir',
            '6_months' => '6 bulan terakhir',
            '1_year'   => '1 tahun terakhir',
        ];

        return back()->with('success', "Berhasil menghapus {$deleted} log yang lebih dari {$labels[$period]}.");
    }
}
