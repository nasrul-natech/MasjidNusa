<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRewardPeriod;
use App\Models\User;
use App\Support\NewFeature;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JamaahAttendanceLeaderboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        abort_unless($user->is_active && $user->isJamaah() && ! $user->isAdmin(), 403);

        $request->validate(['period' => ['nullable', 'string', 'max:30']]);
        $periods = NewFeature::enabled('attendance_rewards')
            ? AttendanceRewardPeriod::whereIn('status', ['active', 'final'])->orderByDesc('starts_at')->orderByDesc('id')->get(['id', 'name', 'status'])
            : collect();
        $period = null;
        if ($request->filled('period') && $request->input('period') !== 'all') {
            abort_unless(NewFeature::enabled('attendance_rewards'), 404);
            $period = AttendanceRewardPeriod::whereIn('status', ['active', 'final'])->findOrFail($request->input('period'));
        }

        $eligible = User::role('jamaah')->where('is_active', true)
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', [
                'super_admin', 'admin_operasional', 'admin_keuangan', 'admin_media_it',
            ]))->select('users.id');
        $cutoff = CarbonImmutable::now();
        if ($period) {
            $cutoff = $period->status === 'final'
                ? ($period->effective_cutoff_at ?? $period->ends_at)
                : $cutoff->min($period->ends_at);
        }
        $ranking = DB::table('activity_attendances as attendance')
            ->join('users', 'users.id', '=', 'attendance.user_id')
            ->whereIn('users.id', $eligible)
            ->where('attendance.checked_in_at', '<', $cutoff)
            ->when($period, fn ($query) => $query->where('attendance.checked_in_at', '>=', $period->starts_at))
            ->select('users.id as user_id', 'users.name as display_name')
            ->selectRaw('COUNT(*) as points, MAX(attendance.checked_in_at) as reached_at')
            ->groupBy('users.id', 'users.name');

        $own = DB::query()->fromSub(clone $ranking, 'ranking')->where('user_id', $user->id)->first();
        $ownRank = null;
        if ($own) {
            $ownRank = 1 + DB::query()->fromSub(clone $ranking, 'ranking')
                ->where(function ($query) use ($own) {
                    $query->where('points', '>', $own->points)
                        ->orWhere(fn ($query) => $query->where('points', $own->points)->where('reached_at', '<', $own->reached_at))
                        ->orWhere(fn ($query) => $query->where('points', $own->points)->where('reached_at', $own->reached_at)->where('user_id', '<', $own->user_id));
                })->count();
        }
        $leaders = $ranking->orderByDesc('points')->orderBy('reached_at')->orderBy('users.id')->paginate(25)->withQueryString();

        return view('user.attendance-leaderboard', compact('leaders', 'periods', 'period', 'own', 'ownRank'));
    }
}
