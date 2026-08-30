<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Donation;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_donations' => Donation::whereIn('status', ['verified', 'paid'])->sum('amount'),
            'today_logins' => AuditLog::where('action', 'user.login')->whereDate('created_at', today())->count(),
        ];

        $recentLogs = AuditLog::with('user')->orderByDesc('created_at')->limit(10)->get();

        return view('super-admin.dashboard', compact('stats', 'recentLogs'));
    }
}
