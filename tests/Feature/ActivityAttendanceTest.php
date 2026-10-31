<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityAttendance;
use App\Models\ActivityAttendanceSetting;
use App\Models\SystemModule;
use App\Models\User;
use App\Services\ActivityAttendanceService;
use App\Support\AttendanceFeaturePermissions;
use App\Support\NewFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ActivityAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function activity(): Activity
    {
        SystemModule::where('slug', 'activity_attendance')->update(['is_active' => true]);
        $activity = Activity::create(['title' => 'Kajian', 'slug' => 'kajian', 'status' => 'publish', 'start_date' => now()->subHour()]);
        ActivityAttendanceSetting::create(['activity_id' => $activity->id, 'enabled' => true, 'duration_seconds' => 120]);

        return $activity;
    }

    private function jamaah(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(Role::findOrCreate('jamaah', 'web'));

        return $user;
    }

    public function test_new_modules_fail_closed_without_changing_legacy_default(): void
    {
        $this->assertFalse(NewFeature::enabled('activity_attendance'));
        $this->get('/api/kegiatan/1/attendance')->assertNotFound();
        SystemModule::where('slug', 'activity_attendance')->delete();
        $this->assertFalse(NewFeature::enabled('activity_attendance'));
        $this->assertTrue(SystemModule::isActive('legacy_unknown'));
    }

    public function test_open_is_idempotent_and_preserves_publication(): void
    {
        $activity = $this->activity();
        $operator = $this->admin('admin_operasional');
        $service = app(ActivityAttendanceService::class);
        $session = $service->open($activity, $operator);
        $this->assertEquals(120, $session->opened_at->diffInSeconds($session->closes_at));
        $deadline = $session->closes_at->toDateTimeString();
        $this->travel(30)->seconds();
        $this->assertSame($session->id, $service->open($activity, $operator)->id);
        $this->assertSame($deadline, $session->fresh()->closes_at->toDateTimeString());
        $this->assertSame('publish', $activity->fresh()->status);
        $this->get('/kegiatan/kajian')->assertOk()->assertSee('Check-in Kegiatan');
    }

    public function test_duplicate_check_in_keeps_one_point_even_after_deadline(): void
    {
        $activity = $this->activity();
        $service = app(ActivityAttendanceService::class);
        $service->open($activity, $this->admin('admin_operasional'));
        $user = $this->jamaah();
        $first = $service->checkIn($activity, $user);
        $this->travel(3)->minutes();
        $this->assertSame($first->id, $service->checkIn($activity, $user)->id);
        $this->assertSame(1, ActivityAttendance::count());
        $this->assertTrue($user->hasAttendanceHistory());
    }

    public function test_exact_deadline_rejects_new_check_in(): void
    {
        $activity = $this->activity();
        $service = app(ActivityAttendanceService::class);
        $session = $service->open($activity, $this->admin('admin_operasional'));
        $this->travelTo($session->closes_at);
        $this->expectException(ValidationException::class);
        $service->checkIn($activity, $this->jamaah());
    }

    public function test_future_activity_cannot_be_opened(): void
    {
        $activity = $this->activity();
        $activity->update(['start_date' => now()->addHour()]);
        $this->expectException(ValidationException::class);
        app(ActivityAttendanceService::class)->open($activity, $this->admin('admin_operasional'));
    }

    public function test_admin_with_jamaah_role_cannot_check_in(): void
    {
        $activity = $this->activity();
        app(ActivityAttendanceService::class)->open($activity, $this->admin('admin_operasional'));
        $user = $this->jamaah();
        $user->assignRole(Role::findOrCreate('admin_media_it', 'web'));
        $this->actingAs($user)->postJson(route('attendance.store', $activity))->assertForbidden();
        $this->assertSame(0, ActivityAttendance::count());
    }

    public function test_guest_state_has_no_personal_data_and_is_not_cached(): void
    {
        $activity = $this->activity();
        $response = $this->getJson(route('attendance.state', $activity))->assertOk()->assertJsonPath('eligible', false);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertJsonMissingPath('user_id')->assertJsonMissingPath('email');
        $this->postJson(route('attendance.store', $activity))->assertUnauthorized();
        $this->get('/admin/operasional/attendance')->assertRedirect('/login');
    }

    public function test_history_only_contains_current_user(): void
    {
        $activity = $this->activity();
        $service = app(ActivityAttendanceService::class);
        $service->open($activity, $this->admin('admin_operasional'));
        $user = $this->jamaah();
        $service->checkIn($activity, $user);
        $service->checkIn($activity, $this->jamaah());
        $this->actingAs($user)->get(route('user.attendance'))->assertOk()->assertViewHas('points', 1);
    }

    private function admin(string $role): User
    {
        $user = User::factory()->create(['is_active' => true, 'two_factor_secret' => 'test', 'two_factor_confirmed_at' => now()]);
        $user->assignRole(Role::findOrCreate($role, 'web'));
        AttendanceFeaturePermissions::provision();

        return $user;
    }

    public function test_operational_permission_and_two_factor_are_required(): void
    {
        $activity = $this->activity();
        $operator = $this->admin('admin_operasional');
        $this->actingAs($operator)->get(route('admin.operasional.attendance.index'))->assertRedirect(route('two-factor.challenge'));
        $this->withSession(['two_factor_verified' => true])->get(route('admin.operasional.attendance.index'))->assertOk();
        $operator->roles->first()->revokePermissionTo('manage_activity_attendance');
        $this->actingAs($operator->fresh())->post(route('admin.operasional.attendance.open', $activity))->assertForbidden();
    }

    public function test_media_it_cannot_open_operational_attendance(): void
    {
        $activity = $this->activity();
        $this->actingAs($this->admin('admin_media_it'))->withSession(['two_factor_verified' => true])
            ->post(route('admin.operasional.attendance.open', $activity))->assertForbidden();
    }

    public function test_configuration_provision_preserves_custom_permissions(): void
    {
        $role = Role::findOrCreate('admin_operasional', 'web');
        $role->givePermissionTo(Permission::findOrCreate('custom_existing_permission', 'web'));
        AttendanceFeaturePermissions::provision();
        AttendanceFeaturePermissions::provision();
        $this->assertTrue($role->fresh()->hasPermissionTo('custom_existing_permission'));
        $this->assertTrue($role->fresh()->hasPermissionTo('manage_activity_attendance'));
        $this->assertFalse(NewFeature::enabled('activity_attendance'));
    }

    public function test_activity_settings_lock_and_archive_guard(): void
    {
        $activity = $this->activity();
        app(ActivityAttendanceService::class)->open($activity, $this->admin('admin_operasional'));
        $media = $this->admin('admin_media_it');
        $role = $media->roles->first();
        $role->givePermissionTo(Permission::findOrCreate('manage_activities', 'web'));
        $data = ['title' => $activity->title, 'start_date' => $activity->start_date->toDateTimeString(), 'status' => 'publish', 'attendance_enabled' => 1, 'attendance_minutes' => 5];
        $this->actingAs($media)->withSession(['two_factor_verified' => true])->put(route('admin.media-it.activities.update', $activity), $data)->assertSessionHasErrors('attendance_minutes');
        $data['attendance_minutes'] = 2;
        $data['status'] = 'arsip';
        $this->put(route('admin.media-it.activities.update', $activity), $data)->assertSessionHasErrors('status');
        $data['status'] = 'publish';
        $data['start_date'] = now()->addDay()->toDateTimeString();
        $this->put(route('admin.media-it.activities.update', $activity), $data)->assertSessionHasErrors('start_date');
        $this->delete(route('admin.media-it.activities.destroy', $activity))->assertSessionHas('error');
        $this->assertSame('publish', $activity->fresh()->status);
    }

    public function test_inactive_user_cannot_create_attendance(): void
    {
        $activity = $this->activity();
        $service = app(ActivityAttendanceService::class);
        $service->open($activity, $this->admin('admin_operasional'));
        $user = $this->jamaah();
        $user->update(['is_active' => false]);
        $this->expectException(HttpException::class);
        $service->checkIn($activity, $user);
    }

    public function test_active_session_remains_listed_across_midnight(): void
    {
        $this->travelTo(now()->startOfDay()->setTime(23, 59, 0));
        $activity = $this->activity();
        app(ActivityAttendanceService::class)->open($activity, $this->admin('admin_operasional'));
        $this->travel(61)->seconds();
        $this->get(route('kegiatan.index'))->assertOk()->assertViewHas('activities', fn ($items) => $items->contains('id', $activity->id));
    }

    public function test_no_retroactive_attendance_for_legacy_activity(): void
    {
        SystemModule::where('slug', 'activity_attendance')->update(['is_active' => true]);
        $activity = Activity::create(['title' => 'Legacy', 'slug' => 'legacy', 'status' => 'publish', 'start_date' => now()->subHour()]);
        $this->get('/kegiatan/legacy')->assertOk()->assertDontSee('Check-in Kegiatan');
        $this->expectException(ValidationException::class);
        app(ActivityAttendanceService::class)->open($activity, $this->admin('admin_operasional'));
    }

    public function test_attendance_user_delete_is_replaced_with_safe_message(): void
    {
        $activity = $this->activity();
        $service = app(ActivityAttendanceService::class);
        $service->open($activity, $this->admin('admin_operasional'));
        $user = $this->jamaah();
        $service->checkIn($activity, $user);
        $super = $this->admin('super_admin');
        $super->givePermissionTo(Permission::findOrCreate('manage_users', 'web'));
        $this->actingAs($super)->withSession(['two_factor_verified' => true])
            ->delete(route('super-admin.users.destroy', $user))->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertSame(1, ActivityAttendance::count());
    }

    public function test_reward_dependency_and_immediate_module_disable(): void
    {
        $activity = $this->activity();
        SystemModule::where('slug', 'attendance_rewards')->update(['is_active' => true]);
        $this->assertTrue(NewFeature::enabled('attendance_rewards'));
        $this->getJson(route('attendance.state', $activity))->assertOk();
        SystemModule::where('slug', 'activity_attendance')->update(['is_active' => false]);
        $this->assertFalse(NewFeature::enabled('attendance_rewards'));
        $this->getJson(route('attendance.state', $activity))->assertNotFound();
    }

    public function test_media_it_can_configure_attendance_without_changing_legacy_status(): void
    {
        $activity = $this->activity();
        $media = $this->admin('admin_media_it');
        $media->roles->first()->givePermissionTo(Permission::findOrCreate('manage_activities', 'web'));
        $this->actingAs($media)->withSession(['two_factor_verified' => true])->put(route('admin.media-it.activities.update', $activity), [
            'title' => 'Kajian diperbarui', 'start_date' => $activity->start_date->toDateTimeString(), 'status' => 'publish',
            'attendance_enabled' => 1, 'attendance_minutes' => 3,
        ])->assertSessionHasNoErrors();
        $this->assertSame(180, $activity->attendanceSetting()->first()->duration_seconds);
        $this->assertSame('publish', $activity->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'activity.attendance_configured']);
    }

    public function test_no_attendance_settings_are_changed_when_feature_is_off(): void
    {
        $activity = $this->activity();
        SystemModule::where('slug', 'activity_attendance')->update(['is_active' => false]);
        $media = $this->admin('admin_media_it');
        $media->roles->first()->givePermissionTo(Permission::findOrCreate('manage_activities', 'web'));
        $this->actingAs($media)->withSession(['two_factor_verified' => true])->put(route('admin.media-it.activities.update', $activity), [
            'title' => 'Konten lama', 'start_date' => $activity->start_date->toDateTimeString(), 'status' => 'publish',
            'attendance_enabled' => 0, 'attendance_minutes' => 99,
        ])->assertSessionHasNoErrors();
        $this->assertSame(120, $activity->attendanceSetting()->first()->duration_seconds);
        $this->assertTrue($activity->attendanceSetting()->first()->enabled);
    }

    public function test_media_it_form_renders_new_and_locked_attendance_settings(): void
    {
        $activity = $this->activity();
        $media = $this->admin('admin_media_it');
        $media->roles->first()->givePermissionTo(Permission::findOrCreate('manage_activities', 'web'));
        $this->actingAs($media)->withSession(['two_factor_verified' => true])
            ->get(route('admin.media-it.activities.create'))->assertOk()->assertSee('Aktifkan check-in jamaah');
        app(ActivityAttendanceService::class)->open($activity, $this->admin('admin_operasional'));
        $this->get(route('admin.media-it.activities.edit', $activity))->assertOk()->assertSee('Terkunci setelah dibuka.');
    }

    public function test_bulk_delete_skips_attendance_history_and_keeps_unrelated_delete_working(): void
    {
        $activity = $this->activity();
        $service = app(ActivityAttendanceService::class);
        $service->open($activity, $this->admin('admin_operasional'));
        $attendee = $this->jamaah();
        $service->checkIn($activity, $attendee);
        $unused = $this->jamaah();
        $super = $this->admin('super_admin');
        $super->givePermissionTo(Permission::findOrCreate('manage_users', 'web'));
        $this->actingAs($super)->withSession(['two_factor_verified' => true])->post(route('super-admin.users.bulk-action'), [
            'action' => 'delete', 'user_ids' => [$attendee->id, $unused->id],
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $attendee->id]);
        $this->assertDatabaseMissing('users', ['id' => $unused->id]);
        $this->assertTrue($attendee->hasAttendanceHistory(true));
    }

    public function test_invalid_check_in_duration_is_rejected_before_updating_activity(): void
    {
        $activity = $this->activity();
        $media = $this->admin('admin_media_it');
        $media->roles->first()->givePermissionTo(Permission::findOrCreate('manage_activities', 'web'));
        foreach ([0, 31, 1.5] as $minutes) {
            $this->actingAs($media)->withSession(['two_factor_verified' => true])->put(route('admin.media-it.activities.update', $activity), [
                'title' => 'Tidak boleh tersimpan', 'start_date' => $activity->start_date->toDateTimeString(), 'status' => 'publish',
                'attendance_enabled' => 1, 'attendance_minutes' => $minutes,
            ])->assertSessionHasErrors('attendance_minutes');
        }
        $this->assertSame('Kajian', $activity->fresh()->title);
        $this->assertSame(120, $activity->attendanceSetting()->first()->duration_seconds);
    }

    public function test_ongoing_activity_stays_visible_on_home_and_agenda_until_manual_completion(): void
    {
        $activity = $this->activity();
        $activity->update(['start_date' => now()->subDays(2), 'end_date' => now()->subDay()]);
        for ($index = 0; $index < 5; $index++) {
            Activity::create(['title' => 'Mendatang '.$index, 'slug' => 'mendatang-'.$index, 'status' => 'publish', 'start_date' => now()->addDays($index + 1)]);
        }
        $this->assertSame('ongoing', $activity->fresh()->attendance_phase);
        $this->get('/')->assertOk()->assertViewHas('upcomingActivities', fn ($items) => $items->first()->id === $activity->id)->assertSee('Sesi Aktif');
        $this->get(route('kegiatan.index'))->assertOk()->assertViewHas('activities', fn ($items) => $items->contains('id', $activity->id));
        $this->get(route('kegiatan.index', ['filter' => 'past']))->assertOk()->assertViewHas('activities', fn ($items) => ! $items->contains('id', $activity->id));
    }

    public function test_media_it_completion_opens_configured_window_and_keeps_public_detail(): void
    {
        $activity = $this->activity();
        $activity->attendanceSetting()->update(['duration_seconds' => 180]);
        $media = $this->admin('admin_media_it');
        $media->givePermissionTo(Permission::findOrCreate('manage_activities', 'web'));
        $this->actingAs($media)->withSession(['two_factor_verified' => true])
            ->get(route('admin.media-it.activities.index'))->assertOk()->assertSee('Selesaikan &amp; Buka Check-in', false);
        $this->post(route('admin.media-it.activities.complete', $activity))->assertSessionHasNoErrors();
        $session = $activity->attendanceSession()->firstOrFail();
        $this->assertSame(180, $session->duration_seconds);
        $this->assertEquals(180, $session->opened_at->diffInSeconds($session->closes_at));
        $this->assertSame('publish', $activity->fresh()->status);
        $this->assertSame('checkin', $activity->fresh()->attendance_phase);
        $this->get('/kegiatan/kajian')->assertOk()->assertSee('Check-in Dibuka');
        $this->travel(30)->seconds();
        $this->post(route('admin.media-it.activities.complete', $activity))->assertSessionHasNoErrors();
        $this->assertTrue($session->closes_at->equalTo($session->fresh()->closes_at));
        $this->travel(4)->minutes();
        $this->assertSame('closed', $activity->fresh()->attendance_phase);
        $this->get(route('kegiatan.index'))->assertViewHas('activities', fn ($items) => ! $items->contains('id', $activity->id));
        $this->get(route('kegiatan.index', ['filter' => 'past']))->assertViewHas('activities', fn ($items) => $items->contains('id', $activity->id));
    }

    public function test_media_it_completion_requires_manage_activities_and_two_factor(): void
    {
        $activity = $this->activity();
        $media = $this->admin('admin_media_it');
        $this->actingAs($media)->withSession(['two_factor_verified' => true])
            ->post(route('admin.media-it.activities.complete', $activity))->assertForbidden();
        $media->givePermissionTo(Permission::findOrCreate('manage_activities', 'web'));
        $this->withSession(['two_factor_verified' => false])->post(route('admin.media-it.activities.complete', $activity))->assertRedirect(route('two-factor.challenge'));
    }

    public function test_public_phase_feed_tracks_manual_completion_without_personal_data(): void
    {
        $activity = $this->activity();
        $draft = Activity::create(['title' => 'Privat', 'slug' => 'private-phase', 'status' => 'draft', 'start_date' => now()]);
        $url = route('attendance.phases', ['ids' => [$activity->id, $draft->id]]);
        $response = $this->getJson($url)->assertOk()->assertJsonCount(2, 'activities')->assertJsonPath('activities.0.phase', 'ongoing')->assertJsonPath('activities.1.phase', 'disabled');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertJsonMissingPath('activities.0.user_id');
        app(ActivityAttendanceService::class)->open($activity, $this->admin('admin_operasional'));
        $this->getJson($url)->assertOk()->assertJsonPath('activities.0.phase', 'checkin');
        $this->getJson(route('attendance.phases', ['ids' => range(1, 21)]))->assertUnprocessable();
    }

    public function test_disabling_feature_preserves_legacy_edits_but_retains_attendance_history(): void
    {
        $activity = $this->activity();
        app(ActivityAttendanceService::class)->open($activity, $this->admin('admin_operasional'));
        SystemModule::where('slug', 'activity_attendance')->update(['is_active' => false]);
        $media = $this->admin('admin_media_it');
        $media->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('manage_activities', 'web'));
        $this->actingAs($media)->withSession(['two_factor_verified' => true])->put(route('admin.media-it.activities.update', $activity), [
            'title' => 'Diarsipkan', 'status' => 'arsip', 'start_date' => now()->addDay()->toDateTimeString(),
        ])->assertSessionHasNoErrors();
        $this->assertSame('arsip', $activity->fresh()->status);
        $this->assertNotNull($activity->attendanceSession()->first());
        $this->delete(route('admin.media-it.activities.destroy', $activity))->assertSessionHas('error');
    }
}
