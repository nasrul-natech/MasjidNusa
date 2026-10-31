<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityAttendance;
use App\Models\ActivityAttendanceSession;
use App\Models\ActivityAttendanceSetting;
use App\Models\AuditLog;
use App\Models\SystemModule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityAttendanceService
{
    public function lockPeriods(): void
    {
        $module = SystemModule::where('slug', 'activity_attendance')->lockForUpdate()->first();
        abort_unless($module?->is_active, 404);
        DB::table('attendance_reward_periods')->orderBy('id')->lockForUpdate()->get();
    }

    public function open(Activity $activity, User $operator): ActivityAttendanceSession
    {
        $authorized = $operator->hasAnyRole(['admin_operasional', 'super_admin']) && $operator->can('manage_activity_attendance');
        $authorized = $authorized || ($operator->hasAnyRole(['admin_media_it', 'super_admin']) && $operator->can('manage_activities'));
        abort_unless($operator->is_active && $authorized, 403);

        return DB::transaction(function () use ($activity, $operator) {
            $this->lockPeriods();
            $activity = Activity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            $session = ActivityAttendanceSession::where('activity_id', $activity->id)->lockForUpdate()->first();
            if ($session) {
                return $session;
            }
            $settings = ActivityAttendanceSetting::where('activity_id', $activity->id)->first();
            if ($activity->status !== 'publish' || ! $settings?->enabled || now()->lt($activity->start_date)) {
                throw ValidationException::withMessages(['attendance' => 'Kegiatan belum dimulai, belum terbit, atau absensi belum diaktifkan.']);
            }
            $now = now()->startOfSecond();
            $session = ActivityAttendanceSession::create([
                'activity_id' => $activity->id, 'completed_at' => $now, 'opened_at' => $now,
                'closes_at' => $now->copy()->addSeconds($settings->duration_seconds),
                'duration_seconds' => $settings->duration_seconds, 'opened_by' => $operator->id,
            ]);
            AuditLog::record('activity.attendance_opened', $session, [], ['closes_at' => $session->closes_at->toIso8601String()]);

            return $session;
        }, 3);
    }

    public function checkIn(Activity $activity, User $user): ActivityAttendance
    {
        return DB::transaction(function () use ($activity, $user) {
            $this->lockPeriods();
            $activity = Activity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            $session = ActivityAttendanceSession::where('activity_id', $activity->id)->lockForUpdate()->first();
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($user->is_active && $user->isJamaah() && ! $user->isAdmin(), 403);
            if (! $session) {
                throw ValidationException::withMessages(['attendance' => 'Check-in belum dibuka.']);
            }
            $existing = ActivityAttendance::where('session_id', $session->id)->where('user_id', $user->id)->first();
            if ($existing) {
                return $existing;
            }
            $now = now();
            if ($activity->status !== 'publish' || $now->lt($session->opened_at) || $now->gte($session->closes_at)) {
                throw ValidationException::withMessages(['attendance' => 'Check-in sudah ditutup atau belum tersedia.']);
            }

            return ActivityAttendance::create(['session_id' => $session->id, 'user_id' => $user->id, 'checked_in_at' => $now]);
        }, 3);
    }
}
