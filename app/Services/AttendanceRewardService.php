<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityAttendanceSession;
use App\Models\AttendanceRewardPeriod;
use App\Models\AttendanceRewardRecipient;
use App\Models\AuditLog;
use App\Models\SystemModule;
use App\Models\User;
use App\Support\NewFeature;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AttendanceRewardService
{
    public function createPeriod(array $data, User $actor): AttendanceRewardPeriod
    {
        $this->authorize($actor, 'manage_attendance_rewards');
        $data = Validator::make($data, [
            'name' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'timezone' => ['required', 'timezone'],
            'winner_count' => ['required', 'integer', Rule::in([3, 10])],
        ])->validate();

        return DB::transaction(function () use ($data, $actor) {
            $this->lockPeriods();
            $start = CarbonImmutable::parse($data['start_date'], $data['timezone'])->startOfDay();
            $end = CarbonImmutable::parse($data['end_date'], $data['timezone'])->addDay()->startOfDay();
            $period = AttendanceRewardPeriod::create($data + [
                'starts_at' => $start->setTimezone(config('app.timezone')),
                'ends_at' => $end->setTimezone(config('app.timezone')),
                'status' => 'draft', 'rules_version' => 'attendance-v1', 'created_by' => $actor->id,
            ]);
            $this->audit('attendance_reward.created', $period, $actor, [], ['status' => 'draft']);

            return $period;
        }, 3);
    }

    public function activate(AttendanceRewardPeriod $period, User $actor): AttendanceRewardPeriod
    {
        $this->authorize($actor, 'manage_attendance_rewards');

        return DB::transaction(function () use ($period, $actor) {
            $periods = $this->lockPeriods();
            $locked = $periods->firstWhere('id', $period->id);
            abort_unless($locked, 404);
            if ($locked->status === 'active') {
                return $locked;
            }
            $this->ensure($locked->status === 'draft', 'Periode final tidak dapat diaktifkan kembali.');
            foreach ($periods as $other) {
                $this->ensure($other->id === $locked->id || $other->status === 'draft'
                    || $locked->starts_at >= $other->ends_at || $locked->ends_at <= $other->starts_at,
                    'Rentang bertumpang tindih dengan periode aktif atau final.');
            }
            $locked->update(['status' => 'active']);
            $this->audit('attendance_reward.activated', $locked, $actor, ['status' => 'draft'], ['status' => 'active']);

            return $locked;
        }, 3);
    }

    public function leaderboard(AttendanceRewardPeriod $period, ?CarbonImmutable $cutoff = null): Collection
    {
        abort_unless(NewFeature::enabled('attendance_rewards'), 404);
        if ($period->status === 'final') {
            return $period->recipients()->get();
        }
        $cutoff ??= CarbonImmutable::now()->min($period->ends_at);

        return $this->rankingQuery($period, $cutoff)->get();
    }

    public function leaderboardPage(AttendanceRewardPeriod $period): Collection|LengthAwarePaginator
    {
        abort_unless(NewFeature::enabled('attendance_rewards'), 404);
        if ($period->status === 'final') {
            return $period->recipients()->get();
        }

        return $this->rankingQuery($period, CarbonImmutable::now()->min($period->ends_at))->paginate(50)->withQueryString();
    }

    private function rankingQuery(AttendanceRewardPeriod $period, CarbonImmutable $cutoff, ?Collection $eligibleIds = null)
    {
        $eligibleIds ??= User::query()->role('jamaah')->where('is_active', true)
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', [
                'super_admin', 'admin_operasional', 'admin_keuangan', 'admin_media_it',
            ]))->select('users.id');

        return DB::table('activity_attendances as attendance')
            ->join('users', 'users.id', '=', 'attendance.user_id')
            ->where('attendance.checked_in_at', '>=', $period->starts_at)
            ->where('attendance.checked_in_at', '<', $cutoff->min($period->ends_at))
            ->whereIn('users.id', $eligibleIds)
            ->select('users.id as user_id', 'users.name as display_name')
            ->selectRaw('COUNT(*) as points, MAX(attendance.checked_in_at) as reached_at')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('points')->orderBy('reached_at')->orderBy('users.id');
    }

    public function finalize(AttendanceRewardPeriod $period, User $actor): AttendanceRewardPeriod
    {
        $this->authorize($actor, 'finalize_attendance_rewards');

        return DB::transaction(function () use ($period, $actor) {
            $locked = $this->lockPeriods()->firstWhere('id', $period->id);
            abort_unless($locked, 404);
            if ($locked->status === 'final') {
                return $locked->load('recipients');
            }
            $this->ensure($locked->status === 'active', 'Aktifkan periode sebelum finalisasi.');
            // Match persisted second precision so stored cutoff exactly reproduces snapshot bounds.
            $now = CarbonImmutable::now()->startOfSecond();
            $this->ensure($now >= $locked->starts_at, 'Periode belum dimulai.');
            // Module mutex prevents new sessions/check-ins while locking existing rows.
            $sessions = ActivityAttendanceSession::query()
                ->where('opened_at', '<', $locked->ends_at)
                ->where('closes_at', '>', $locked->starts_at)->orderBy('id')->get();
            Activity::whereIn('id', $sessions->pluck('activity_id'))->orderBy('id')->lockForUpdate()->get();
            $sessions = ActivityAttendanceSession::whereIn('id', $sessions->pluck('id'))
                ->orderBy('id')->lockForUpdate()->get();
            $this->ensure(! $sessions->contains(fn ($session) => $session->opened_at <= $now && $now < $session->closes_at),
                'Masih ada sesi absensi terbuka dalam rentang periode.');
            $cutoff = $now->min($locked->ends_at);
            // Lock candidate accounts so eligibility cannot change during snapshot creation.
            $userIds = DB::table('activity_attendances')->where('checked_in_at', '>=', $locked->starts_at)
                ->where('checked_in_at', '<', $cutoff)->pluck('user_id')->unique();
            $candidates = User::whereIn('id', $userIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $eligibleIds = $candidates->filter(function (User $user) {
                // Current locking reads avoid stale eligibility under MySQL REPEATABLE READ.
                $roles = $user->roles()->orderBy('roles.id')->lockForUpdate()->get()->pluck('name');

                return $user->is_active && $roles->contains('jamaah') && ! $roles->intersect([
                    'super_admin', 'admin_operasional', 'admin_keuangan', 'admin_media_it',
                ])->count();
            })->pluck('id');
            foreach ($this->rankingQuery($locked, $cutoff, $eligibleIds)->limit($locked->winner_count)->get() as $index => $row) {
                $locked->recipients()->create([
                    'user_id' => $row->user_id, 'rank' => $index + 1, 'display_name' => $candidates[$row->user_id]->name,
                    'points' => $row->points, 'reached_at' => $row->reached_at, 'reward_status' => 'pending',
                ]);
            }
            $locked->update([
                'status' => 'final', 'effective_cutoff_at' => $cutoff,
                'finalized_at' => $now, 'finalized_by' => $actor->id,
            ]);
            $this->audit('attendance_reward.finalized', $locked, $actor, ['status' => 'active'], [
                'status' => 'final', 'effective_cutoff_at' => $cutoff->toIso8601String(),
            ]);

            return $locked->load('recipients');
        }, 3);
    }

    public function updateRewardStatus(AttendanceRewardRecipient $recipient, string $status, User $actor): AttendanceRewardRecipient
    {
        $this->authorize($actor, 'manage_attendance_rewards');
        Validator::make(['reward_status' => $status], [
            'reward_status' => ['required', Rule::in(['pending', 'ready', 'delivered'])],
        ])->validate();

        return DB::transaction(function () use ($recipient, $status, $actor) {
            $period = $this->lockPeriods()->firstWhere('id', $recipient->period_id);
            $this->ensure($period?->status === 'final', 'Hadiah hanya dapat dicatat untuk periode final.');
            $locked = AttendanceRewardRecipient::whereKey($recipient->id)->lockForUpdate()->firstOrFail();
            if ($locked->reward_status === $status) {
                return $locked;
            }
            $this->ensure($locked->reward_status !== 'delivered', 'Hadiah yang sudah diserahkan tidak dapat diubah.');
            $old = $locked->reward_status;
            $now = CarbonImmutable::now();
            $locked->update([
                'reward_status' => $status, 'status_changed_by' => $actor->id, 'status_changed_at' => $now,
                'delivered_by' => $status === 'delivered' ? $actor->id : null,
                'delivered_at' => $status === 'delivered' ? $now : null,
            ]);
            $this->audit('attendance_reward.status_changed', $locked, $actor,
                ['reward_status' => $old], ['reward_status' => $status]);

            return $locked;
        }, 3);
    }

    private function lockPeriods(): Collection
    {
        $module = SystemModule::where('slug', 'activity_attendance')->lockForUpdate()->first();
        abort_unless($module && $module->is_active && NewFeature::enabled('attendance_rewards'), 404);

        return AttendanceRewardPeriod::orderBy('id')->lockForUpdate()->get();
    }

    private function authorize(User $actor, string $permission): void
    {
        abort_unless(NewFeature::enabled('attendance_rewards'), 404);
        abort_unless($actor->is_active && $actor->hasAnyRole(['admin_operasional', 'super_admin'])
            && $actor->can($permission), 403);
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['period' => $message]);
        }
    }

    private function audit(string $action, $model, User $actor, array $old, array $new): void
    {
        AuditLog::create([
            'user_id' => $actor->id, 'action' => $action, 'auditable_type' => $model::class,
            'auditable_id' => $model->id, 'old_values' => $old ?: null, 'new_values' => $new,
        ]);
    }
}
