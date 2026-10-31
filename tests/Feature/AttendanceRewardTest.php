<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityAttendance;
use App\Models\ActivityAttendanceSession;
use App\Models\ActivityAttendanceSetting;
use App\Models\AttendanceRewardPeriod;
use App\Models\AuditLog;
use App\Models\SystemModule;
use App\Models\User;
use App\Services\ActivityAttendanceService;
use App\Services\AttendanceRewardService;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AttendanceRewardTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private AttendanceRewardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-30 12:00:00', config('app.timezone')));
        $this->travelTo(CarbonImmutable::now());
        foreach (['jamaah', 'admin_operasional', 'admin_media_it', 'super_admin', 'admin_keuangan'] as $name) {
            Role::findOrCreate($name, 'web');
        }
        foreach (['view_attendance_rewards', 'manage_attendance_rewards', 'finalize_attendance_rewards', 'manage_activity_attendance'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
        foreach (['activity_attendance', 'attendance_rewards'] as $slug) {
            SystemModule::updateOrCreate(['slug' => $slug], ['name' => $slug, 'is_active' => true, 'is_core' => false]);
        }
        $this->operator = User::factory()->create(['is_active' => true]);
        $this->operator->assignRole('admin_operasional');
        $this->operator->givePermissionTo(['view_attendance_rewards', 'manage_attendance_rewards', 'finalize_attendance_rewards', 'manage_activity_attendance']);
        $this->service = app(AttendanceRewardService::class);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    private function period(array $overrides = []): AttendanceRewardPeriod
    {
        return $this->service->createPeriod($overrides + [
            'name' => 'Oktober', 'start_date' => '2026-10-01', 'end_date' => '2026-10-31',
            'timezone' => 'Asia/Jakarta', 'winner_count' => 3,
        ], $this->operator);
    }

    private function jamaah(array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['is_active' => true]);
        $user->assignRole('jamaah');

        return $user;
    }

    private function attendance(User $user, string $at): ActivityAttendance
    {
        $time = CarbonImmutable::parse($at, config('app.timezone'));
        $activity = Activity::create([
            'title' => 'Kajian', 'slug' => 'kajian-'.uniqid(), 'description' => 'Kajian',
            'status' => 'publish', 'start_date' => $time->subHour(), 'created_by' => $this->operator->id,
        ]);
        $session = ActivityAttendanceSession::create([
            'activity_id' => $activity->id, 'completed_at' => $time->subMinute(),
            'opened_at' => $time->subMinute(), 'closes_at' => $time->addMinute(),
            'duration_seconds' => 120, 'opened_by' => $this->operator->id,
        ]);

        return ActivityAttendance::create(['session_id' => $session->id, 'user_id' => $user->id, 'checked_in_at' => $time]);
    }

    public function test_timezone_bounds_are_inclusive_dates_and_exclusive_next_day(): void
    {
        $period = $this->period(['start_date' => '2026-10-29', 'end_date' => '2026-10-29', 'timezone' => 'Asia/Makassar']);
        $this->assertSame('2026-10-28 23:00:00', $period->starts_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-29 23:00:00', $period->ends_at->format('Y-m-d H:i:s'));
        $user = $this->jamaah();
        $this->attendance($user, '2026-10-28 22:59:59');
        $this->attendance($user, '2026-10-28 23:00:00');
        $this->attendance($user, '2026-10-29 22:59:59');
        $this->attendance($user, '2026-10-29 23:00:00');
        $this->assertSame(2, (int) $this->service->leaderboard($period)->sole()->points);
    }

    public function test_overlap_rejected_on_activation_including_final_but_adjacent_allowed(): void
    {
        $first = $this->period(['end_date' => '2026-10-10']);
        $this->service->activate($first, $this->operator);
        $this->service->finalize($first, $this->operator);
        $adjacent = $this->period(['start_date' => '2026-10-11']);
        $this->assertSame('active', $this->service->activate($adjacent, $this->operator)->status);
        $overlap = $this->period(['start_date' => '2026-10-10', 'end_date' => '2026-10-10']);
        $this->expectException(ValidationException::class);
        $this->service->activate($overlap, $this->operator);
    }

    public function test_ranking_eligibility_snapshot_idempotency_and_manual_delivery(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $first = $this->jamaah(['name' => 'Pertama']);
        $second = $this->jamaah(['name' => 'Kedua']);
        $third = $this->jamaah();
        $inactive = $this->jamaah(['is_active' => false]);
        $admin = $this->jamaah();
        $admin->assignRole('admin_media_it');
        $zero = $this->jamaah();
        foreach ([$second, $first] as $user) {
            $this->attendance($user, '2026-10-10 10:00:00');
            $this->attendance($user, '2026-10-11 10:00:00');
        }
        $this->attendance($third, '2026-10-10 08:00:00');
        $this->attendance($inactive, '2026-10-10 08:00:00');
        $this->attendance($admin, '2026-10-10 08:00:00');
        $final = $this->service->finalize($period, $this->operator);
        $this->assertSame([$first->id, $second->id, $third->id], $final->recipients->pluck('user_id')->all());
        $this->assertFalse($final->recipients->contains('user_id', $zero->id));
        $first->update(['name' => 'Nama berubah', 'is_active' => false]);
        $this->attendance($third, '2026-10-30 12:01:00');
        $again = $this->service->finalize($period, $this->operator);
        $this->assertSame('Pertama', $again->recipients->first()->display_name);
        $this->assertSame(2, $again->recipients->first()->points);
        $this->assertSame(3, $again->recipients->count());
        $this->assertSame(1, AuditLog::where('action', 'attendance_reward.finalized')->count());
        $recipient = $again->recipients->first();
        $ready = $this->service->updateRewardStatus($recipient, 'ready', $this->operator);
        $this->assertSame($this->operator->id, $ready->status_changed_by);
        $delivered = $this->service->updateRewardStatus($recipient, 'delivered', $this->operator);
        $this->assertSame($this->operator->id, $delivered->delivered_by);
        $this->assertNotNull($delivered->delivered_at);
        $this->service->updateRewardStatus($recipient, 'delivered', $this->operator);
        $this->assertSame(2, AuditLog::where('action', 'attendance_reward.status_changed')->count());
        $this->expectException(ValidationException::class);
        $this->service->updateRewardStatus($recipient, 'pending', $this->operator);
    }

    public function test_earlier_reached_time_breaks_tie_before_account_id_and_ten_cap(): void
    {
        $period = $this->service->activate($this->period(['winner_count' => 10]), $this->operator);
        $lowerId = $this->jamaah();
        $earlier = $this->jamaah();
        $this->attendance($lowerId, '2026-10-12 10:00:00');
        $this->attendance($earlier, '2026-10-11 10:00:00');
        $this->assertSame([$earlier->id, $lowerId->id], $this->service->finalize($period, $this->operator)->recipients->pluck('user_id')->all());
    }

    public function test_open_session_blocks_early_cutoff_then_closed_session_allows_it(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $attendance = $this->attendance($this->jamaah(), '2026-10-30 12:00:00');
        try {
            $this->service->finalize($period, $this->operator);
            $this->fail('Open session must block cutoff.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('terbuka', $exception->getMessage());
        }
        $this->assertDatabaseCount('attendance_reward_recipients', 0);
        $this->assertSame('active', $period->fresh()->status);
        $this->travelTo(now()->addMinutes(2));
        $final = $this->service->finalize($period, $this->operator);
        $this->assertSame(1, $final->recipients->count());
        $this->assertSame($attendance->user_id, $final->recipients->sole()->user_id);
    }

    public function test_cutoff_at_end_excludes_future_or_end_boundary_checkins(): void
    {
        $period = $this->service->activate($this->period(['end_date' => '2026-10-29']), $this->operator);
        $this->attendance($this->jamaah(), '2026-10-30 00:00:00');
        $final = $this->service->finalize($period, $this->operator);
        $this->assertSame('2026-10-30 00:00:00', $final->effective_cutoff_at->format('Y-m-d H:i:s'));
        $this->assertCount(0, $final->recipients);
    }

    public function test_post_cutoff_attendance_remains_history_without_changing_final_snapshot(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $this->service->finalize($period, $this->operator);
        $this->travelTo(now()->addMinute());
        $activity = Activity::create([
            'title' => 'Kajian setelah cut-off', 'slug' => 'after-cutoff', 'status' => 'publish',
            'start_date' => now()->subHour(), 'created_by' => $this->operator->id,
        ]);
        ActivityAttendanceSetting::create([
            'activity_id' => $activity->id, 'enabled' => true, 'duration_seconds' => 120,
        ]);
        $attendanceService = app(ActivityAttendanceService::class);
        $attendanceService->open($activity, $this->operator);
        $user = $this->jamaah();
        $attendanceService->checkIn($activity, $user);
        $this->assertDatabaseHas('activity_attendances', ['user_id' => $user->id]);
        $this->assertCount(0, $this->service->leaderboard($period->fresh()));
        $this->assertCount(0, $this->service->finalize($period, $this->operator)->recipients);
    }

    public function test_snapshot_fields_and_active_rules_cannot_be_mutated(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $this->expectException(\LogicException::class);
        $period->update(['winner_count' => 10]);
    }

    public function test_three_and_ten_winner_limits(): void
    {
        foreach (range(1, 11) as $index) {
            $this->attendance($this->jamaah(), '2026-10-10 10:00:00');
        }
        $period = $this->service->activate($this->period(), $this->operator);
        $this->assertCount(3, $this->service->finalize($period, $this->operator)->recipients);
        $next = $this->service->activate($this->period([
            'start_date' => '2026-11-01', 'end_date' => '2026-11-30', 'winner_count' => 10,
        ]), $this->operator);
        foreach (User::role('jamaah')->get() as $user) {
            $this->attendance($user, '2026-11-10 10:00:00');
        }
        $this->travelTo(CarbonImmutable::parse('2026-12-01 12:00:00', config('app.timezone')));
        $this->assertCount(10, $this->service->finalize($next, $this->operator)->recipients);
    }

    public function test_recipient_snapshot_cannot_be_edited(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $this->attendance($this->jamaah(), '2026-10-10 10:00:00');
        $recipient = $this->service->finalize($period, $this->operator)->recipients->sole();
        $this->expectException(\LogicException::class);
        $recipient->update(['points' => 999]);
    }

    public function test_final_period_cannot_be_changed_or_reactivated(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $final = $this->service->finalize($period, $this->operator);
        try {
            $this->service->activate($final, $this->operator);
            $this->fail('Final period must not reactivate.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('final', $exception->getMessage());
        }
        $this->expectException(\LogicException::class);
        $final->update(['effective_cutoff_at' => now()->addDay()]);
    }

    public function test_retention_prevents_deleting_snapshots_and_referenced_accounts(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $user = $this->jamaah();
        $this->attendance($user, '2026-10-10 10:00:00');
        $recipient = $this->service->finalize($period, $this->operator)->recipients->sole();
        try {
            $recipient->delete();
            $this->fail('Snapshot must remain.');
        } catch (\LogicException $exception) {
            $this->assertStringContainsString('dihapus', $exception->getMessage());
        }
        $this->expectException(QueryException::class);
        DB::table('users')->where('id', $user->id)->delete();
    }

    public function test_period_before_start_and_draft_cannot_finalize(): void
    {
        $period = $this->period(['start_date' => '2026-11-01', 'end_date' => '2026-11-30']);
        try {
            $this->service->finalize($period, $this->operator);
            $this->fail('Draft must not finalize.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('Aktifkan', $exception->getMessage());
        }
        $this->service->activate($period, $this->operator);
        $this->expectException(ValidationException::class);
        $this->service->finalize($period, $this->operator);
    }

    public function test_invalid_winner_count_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->period(['winner_count' => 5]);
    }

    public function test_dst_day_uses_calendar_boundary_not_fixed_twenty_four_hours(): void
    {
        $period = $this->period([
            'start_date' => '2026-11-01', 'end_date' => '2026-11-01', 'timezone' => 'America/New_York',
        ]);
        $this->assertSame(25, (int) $period->starts_at->diffInHours($period->ends_at));
    }

    public function test_cutoff_matches_persisted_second_precision(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $this->attendance($this->jamaah(), '2026-10-30 12:00:00');
        ActivityAttendanceSession::query()->update(['closes_at' => '2026-10-30 12:00:00']);
        $this->travelTo(CarbonImmutable::parse('2026-10-30 12:00:00.900000', config('app.timezone')));
        $final = $this->service->finalize($period, $this->operator);
        $this->assertCount(0, $final->recipients);
        $this->assertSame('2026-10-30 12:00:00', $final->fresh()->effective_cutoff_at->format('Y-m-d H:i:s'));
    }

    public function test_inactive_operator_and_admin_without_permission_are_denied(): void
    {
        $this->operator->update(['is_active' => false]);
        try {
            $this->period();
            $this->fail('Inactive operator must be denied.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->operator->update(['is_active' => true]);
        $this->operator->revokePermissionTo('manage_attendance_rewards');
        $this->expectException(HttpException::class);
        $this->period();
    }

    public function test_disabled_dependency_fails_closed_even_for_service(): void
    {
        SystemModule::where('slug', 'activity_attendance')->update(['is_active' => false]);
        $this->expectException(HttpException::class);
        $this->period();
    }

    public function test_routes_protect_private_data_and_render_admin_preview(): void
    {
        $period = $this->period();
        $url = route('admin.operasional.attendance-rewards.show', $period);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($this->jamaah())->get($url)->assertForbidden();
        $this->operator->forceFill(['two_factor_secret' => encrypt('secret'), 'two_factor_confirmed_at' => now()])->save();
        $response = $this->actingAs($this->operator)->withSession(['two_factor_verified' => true])
            ->get($url)->assertOk()->assertSee('Pratinjau');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->service->activate($period, $this->operator);
        $this->attendance($this->jamaah(['name' => '<script>unsafe</script>']), '2026-10-10 10:00:00');
        $this->get($url)->assertOk()->assertSee('Calon penerima')->assertDontSee('<script>unsafe</script>', false);
        $this->service->finalize($period, $this->operator);
        $this->get($url)->assertOk()->assertSee('Snapshot tetap')->assertSee('Belum diproses');
        $this->get(route('admin.operasional.attendance-rewards.index'))->assertOk()->assertSee('Oktober');
    }

    public function test_route_permissions_and_feature_flag_are_required(): void
    {
        $period = $this->period();
        $this->operator->forceFill(['two_factor_secret' => encrypt('secret'), 'two_factor_confirmed_at' => now()])->save();
        $this->operator->revokePermissionTo('finalize_attendance_rewards');
        $this->actingAs($this->operator)->withSession(['two_factor_verified' => true])->post(
            route('admin.operasional.attendance-rewards.finalize', $period), ['confirm' => 1]
        )->assertForbidden();
        SystemModule::where('slug', 'attendance_rewards')->delete();
        $this->get(route('admin.operasional.attendance-rewards.show', $period))->assertNotFound();
    }

    public function test_reward_routes_keep_two_factor_and_finalization_confirmation(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $this->actingAs($this->operator)->get(route('admin.operasional.attendance-rewards.show', $period))
            ->assertRedirect(route('two-factor.setup'));
        $this->operator->forceFill(['two_factor_secret' => 'test', 'two_factor_confirmed_at' => now()])->save();
        $this->get(route('admin.operasional.attendance-rewards.show', $period))
            ->assertRedirect(route('two-factor.challenge'));
        $this->withSession(['two_factor_verified' => true])->post(
            route('admin.operasional.attendance-rewards.finalize', $period)
        )->assertSessionHasErrors('confirm');
        $this->assertSame('active', $period->fresh()->status);
    }

    public function test_recipient_from_another_period_cannot_be_changed_through_route(): void
    {
        $period = $this->service->activate($this->period(), $this->operator);
        $this->attendance($this->jamaah(), '2026-10-10 10:00:00');
        $recipient = $this->service->finalize($period, $this->operator)->recipients->sole();
        $other = $this->period();
        $this->operator->forceFill(['two_factor_secret' => 'test', 'two_factor_confirmed_at' => now()])->save();
        $this->actingAs($this->operator)->withSession(['two_factor_verified' => true])->patch(
            route('admin.operasional.attendance-rewards.recipients.update', [$other, $recipient]),
            ['reward_status' => 'delivered']
        )->assertNotFound();
        $this->assertSame('pending', $recipient->fresh()->reward_status);
    }
}
