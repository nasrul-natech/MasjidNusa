<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityAttendance;
use App\Services\ActivityAttendanceService;
use Illuminate\Http\Request;

class ActivityAttendanceController extends Controller
{
    public function phases(Request $request)
    {
        $data = $request->validate(['ids' => ['required', 'array', 'max:20'], 'ids.*' => ['required', 'integer', 'min:1']]);
        $activities = Activity::published()->whereIn('id', $data['ids'])->with(['attendanceSetting', 'attendanceSession'])->get();

        $activities = $activities->keyBy('id');

        return response()->json(['server_now' => now()->getTimestampMs(), 'activities' => collect($data['ids'])->unique()->values()->map(function ($id) use ($activities) {
            $activity = $activities->get($id);

            return [
                'id' => (int) $id, 'phase' => $activity?->attendance_phase ?? 'disabled',
                'starts_at' => $activity?->start_date->getTimestampMs(), 'closes_at' => $activity?->attendanceSession?->closes_at->getTimestampMs(),
            ];
        })]);
    }

    public function state(Activity $activity, Request $request)
    {
        abort_unless($activity->status === 'publish', 404);
        $session = $activity->attendanceSession;
        $user = $request->user();
        $eligible = $user && $user->is_active && $user->isJamaah() && ! $user->isAdmin();
        $checkedIn = $user && $session && ActivityAttendance::where('session_id', $session->id)->where('user_id', $user->id)->exists();

        return response()->json([
            'enabled' => (bool) $activity->attendanceSetting?->enabled,
            'server_now' => now()->toIso8601String(), 'starts_at' => $activity->start_date->toIso8601String(),
            'opened_at' => $session?->opened_at->toIso8601String(), 'closes_at' => $session?->closes_at->toIso8601String(),
            'eligible' => (bool) $eligible, 'checked_in' => (bool) $checkedIn,
        ]);
    }

    public function store(Activity $activity, Request $request, ActivityAttendanceService $service)
    {
        $attendance = $service->checkIn($activity, $request->user());

        return response()->json(['message' => 'Check-in berhasil. 1 poin kehadiran tercatat.', 'checked_in_at' => $attendance->checked_in_at->toIso8601String()]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        abort_unless($user->is_active && $user->isJamaah() && ! $user->isAdmin(), 403);
        $query = ActivityAttendance::where('user_id', $user->id);
        $points = (clone $query)->count();
        $attendances = $query->with('session.activity')->orderByDesc('checked_in_at')->paginate(20);

        return view('user.attendance', compact('points', 'attendances'));
    }
}
