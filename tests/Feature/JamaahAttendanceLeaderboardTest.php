<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityAttendance;
use App\Models\ActivityAttendanceSession;
use App\Models\AttendanceRewardPeriod;
use App\Models\SystemModule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JamaahAttendanceLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_links_to_leaderboard_for_jamaah(): void
    {
        $user = $this->jamaah();
        $this->actingAs($user)->get(route('user.profile'))->assertOk()->assertSee(route('user.attendance.leaderboard'), false);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-30 12:00:00', config('app.timezone')));
        foreach (['jamaah', 'admin_operasional', 'admin_media_it', 'admin_keuangan', 'super_admin'] as $role) {
            Role::findOrCreate($role, 'web');
        }
        foreach (['activity_attendance', 'attendance_rewards'] as $slug) {
            SystemModule::updateOrCreate(['slug' => $slug], ['name' => $slug, 'is_active' => true, 'is_core' => false]);
        }
    }

    private function jamaah(array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['is_active' => true]);
        $user->assignRole('jamaah');

        return $user;
    }

    private function attendance(User $user, string $at): void
    {
        $time = CarbonImmutable::parse($at, config('app.timezone'));
        $activity = Activity::create([
            'title' => 'Kajian', 'slug' => 'kajian-'.uniqid(), 'description' => 'Kajian',
            'status' => 'publish', 'start_date' => $time->subHour(), 'created_by' => $user->id,
        ]);
        $session = ActivityAttendanceSession::create([
            'activity_id' => $activity->id, 'completed_at' => $time->subMinute(),
            'opened_at' => $time->subMinute(), 'closes_at' => $time->addMinute(),
            'duration_seconds' => 120, 'opened_by' => $user->id,
        ]);
        ActivityAttendance::create(['session_id' => $session->id, 'user_id' => $user->id, 'checked_in_at' => $time]);
    }

    private function period(User $creator, string $status = 'active'): AttendanceRewardPeriod
    {
        return AttendanceRewardPeriod::create([
            'name' => 'Oktober', 'status' => $status, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31',
            'timezone' => 'Asia/Jakarta', 'starts_at' => '2026-10-01 00:00:00', 'ends_at' => '2026-11-01 00:00:00',
            'winner_count' => 3, 'rules_version' => 'v1', 'created_by' => $creator->id,
            'effective_cutoff_at' => $status === 'final' ? '2026-10-15 12:00:00' : null,
        ]);
    }

    public function test_requires_login_active_jamaah_without_any_admin_role_and_overall_gate(): void
    {
        $url = route('user.attendance.leaderboard');
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['is_active' => true]))->get($url)->assertForbidden();
        $this->actingAs($this->jamaah(['is_active' => false]))->get($url)->assertRedirect(route('login'));
        foreach (['admin_operasional', 'admin_media_it', 'admin_keuangan', 'super_admin'] as $role) {
            $admin = $this->jamaah();
            $admin->assignRole($role);
            $this->actingAs($admin)->get($url)->assertForbidden();
        }
        $user = $this->jamaah();
        $response = $this->actingAs($user)->get($url)->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        SystemModule::where('slug', 'activity_attendance')->update(['is_active' => false]);
        $this->get($url)->assertNotFound();
    }

    public function test_all_time_default_eligibility_ties_and_privacy(): void
    {
        $first = $this->jamaah(['name' => 'First <script>name</script>']);
        $second = $this->jamaah(['name' => 'Second']);
        $late = $this->jamaah(['name' => 'Later']);
        $inactive = $this->jamaah(['name' => 'Hidden inactive', 'is_active' => false]);
        $admin = $this->jamaah(['name' => 'Hidden admin']);
        $admin->assignRole('admin_operasional');
        foreach ([$first, $second, $inactive, $admin] as $user) {
            $this->attendance($user, '2026-09-01 10:00:00');
        }
        $this->attendance($late, '2026-09-01 11:00:00');
        $this->actingAs($second)->get(route('user.attendance.leaderboard'))
            ->assertOk()->assertViewHas('period', null)->assertViewHas('ownRank', 2)
            ->assertViewHas('leaders', fn ($leaders) => $leaders->pluck('user_id')->all() === [$first->id, $second->id, $late->id])
            ->assertSee('First &lt;script&gt;name&lt;/script&gt;', false)
            ->assertDontSee('<script>name</script>', false)
            ->assertDontSee($first->email)->assertDontSee($first->phone ?? 'never-render-phone')
            ->assertDontSee('Hidden inactive')->assertDontSee('Hidden admin')
            ->assertSee('sesama jamaah yang login')->assertSee('Tidak ditampilkan di TV publik');
    }

    public function test_pagination_keeps_absolute_rank_and_own_rank_off_page(): void
    {
        $users = collect();
        for ($i = 0; $i < 27; $i++) {
            $user = $this->jamaah(['name' => 'Peserta '.$i]);
            $users->push($user);
            $this->attendance($user, '2026-10-10 10:00:00');
        }
        $this->actingAs($users->last())->get(route('user.attendance.leaderboard'))
            ->assertOk()->assertViewHas('ownRank', 27)
            ->assertViewHas('leaders', fn ($leaders) => $leaders->count() === 25 && $leaders->total() === 27);
        $this->get(route('user.attendance.leaderboard', ['period' => 'all', 'page' => 2]))
            ->assertOk()->assertSee('#26')->assertSee('#27')
            ->assertViewHas('leaders', fn ($leaders) => $leaders->count() === 2);
    }

    public function test_period_bounds_final_cutoff_and_nonwinner_rank(): void
    {
        $user = $this->jamaah();
        $period = $this->period($user, 'final');
        foreach (['2026-09-30 23:59:59', '2026-10-01 00:00:00', '2026-10-15 11:59:59', '2026-10-15 12:00:00'] as $at) {
            $this->attendance($user, $at);
        }
        $this->actingAs($user)->get(route('user.attendance.leaderboard', ['period' => $period->id]))
            ->assertOk()->assertViewHas('ownRank', 1)->assertViewHas('own', fn ($own) => (int) $own->points === 2)
            ->assertSee('bukan daftar penerima hadiah')->assertSee('waktu penutupan periode');
        $this->assertCount(0, $period->recipients);
    }

    public function test_rewards_gate_draft_and_unknown_period_and_zero_points(): void
    {
        $user = $this->jamaah();
        $draft = $this->period($user, 'draft');
        $this->actingAs($user)->get(route('user.attendance.leaderboard'))
            ->assertOk()->assertSee('Belum berperingkat')->assertViewHas('ownRank', null)
            ->assertViewHas('periods', fn ($periods) => $periods->isEmpty());
        $this->get(route('user.attendance.leaderboard', ['period' => $draft->id]))->assertNotFound();
        $this->get(route('user.attendance.leaderboard', ['period' => 999999]))->assertNotFound();
        SystemModule::where('slug', 'attendance_rewards')->update(['is_active' => false]);
        $this->get(route('user.attendance.leaderboard'))->assertOk()->assertViewHas('periods', fn ($periods) => $periods->isEmpty());
        $this->get(route('user.attendance.leaderboard', ['period' => $draft->id]))->assertNotFound();
    }

    public function test_profile_menu_links_to_leaderboard_for_jamaah_only(): void
    {
        $user = $this->jamaah();
        $this->actingAs($user)->get(route('user.attendance'))->assertOk()->assertSee(route('user.attendance.leaderboard'));
        $user->assignRole('admin_operasional');
        $this->actingAs($user);
        $this->blade('<x-profile-dropdown />')->assertDontSee(route('user.attendance.leaderboard'));
    }

    public function test_active_period_points_descending_and_half_open_bounds(): void
    {
        $first = $this->jamaah();
        $second = $this->jamaah();
        $period = $this->period($first);
        $this->attendance($first, '2026-10-01 00:00:00');
        $this->attendance($second, '2026-09-30 23:59:59');
        $this->attendance($second, '2026-10-02 10:00:00');
        $this->attendance($second, '2026-10-03 10:00:00');
        $this->attendance($first, '2026-11-01 00:00:00');
        $this->actingAs($first)->get(route('user.attendance.leaderboard', ['period' => $period->id]))
            ->assertOk()->assertViewHas('ownRank', 2)
            ->assertViewHas('leaders', fn ($leaders) => $leaders->pluck('user_id')->all() === [$second->id, $first->id])
            ->assertViewHas('own', fn ($own) => (int) $own->points === 1);
    }
}
