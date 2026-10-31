<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRewardPeriod;
use App\Models\AttendanceRewardRecipient;
use App\Services\AttendanceRewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceRewardController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->is_active, 403);

        return response()->view('admin.operasional.attendance-rewards.index', [
            'periods' => AttendanceRewardPeriod::latest('id')->paginate(20),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request, AttendanceRewardService $service)
    {
        $period = $service->createPeriod($request->all(), $request->user());

        return redirect()->route('admin.operasional.attendance-rewards.show', $period)
            ->with('success', 'Draft periode dibuat. Periksa sebelum aktivasi.');
    }

    public function show(Request $request, AttendanceRewardPeriod $period, AttendanceRewardService $service)
    {
        abort_unless($request->user()->is_active, 403);
        $request->validate(['user_id' => ['nullable', 'integer', 'min:1']]);
        $leaderboard = $service->leaderboardPage($period);
        $details = null;
        if ($request->filled('user_id')) {
            $details = DB::table('activity_attendances as attendance')
                ->join('activity_attendance_sessions as sessions', 'sessions.id', '=', 'attendance.session_id')
                ->join('activities', 'activities.id', '=', 'sessions.activity_id')
                ->where('attendance.user_id', $request->integer('user_id'))
                ->where('attendance.checked_in_at', '>=', $period->starts_at)
                ->where('attendance.checked_in_at', '<', $period->effective_cutoff_at ?? $period->ends_at)
                ->select('activities.title', 'attendance.checked_in_at')
                ->orderBy('attendance.checked_in_at')->paginate(30, ['*'], 'attendance_page')->withQueryString();
        }

        return response()->view('admin.operasional.attendance-rewards.show', compact('period', 'leaderboard', 'details'))
            ->header('Cache-Control', 'no-store, private');
    }

    public function activate(Request $request, AttendanceRewardPeriod $period, AttendanceRewardService $service)
    {
        $service->activate($period, $request->user());

        return back()->with('success', 'Periode aktif. Rentang dan aturan kini terkunci.');
    }

    public function finalize(Request $request, AttendanceRewardPeriod $period, AttendanceRewardService $service)
    {
        $request->validate(['confirm' => ['accepted']]);
        $service->finalize($period, $request->user());

        return back()->with('success', 'Snapshot penerima tersimpan. Tidak ada pembayaran otomatis.');
    }

    public function updateStatus(Request $request, AttendanceRewardPeriod $period, AttendanceRewardRecipient $recipient, AttendanceRewardService $service)
    {
        abort_unless($recipient->period_id === $period->id, 404);
        $data = $request->validate(['reward_status' => ['required', 'string']]);
        $service->updateRewardStatus($recipient, $data['reward_status'], $request->user());

        return back()->with('success', 'Status hadiah dicatat.');
    }
}
