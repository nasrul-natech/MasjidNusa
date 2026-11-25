<?php

namespace Tests\Feature;

use App\Models\MosqueDisplay;
use App\Models\PrayerLocation;
use App\Models\PrayerSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use App\Models\SystemModule;
use App\Models\User;
use App\Services\MosqueDisplayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MosqueDisplayTest extends TestCase
{
    use RefreshDatabase;

    private MosqueDisplayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        SystemModule::updateOrCreate(['slug' => 'mosque_display'], ['name' => 'Display', 'is_active' => true, 'is_core' => false]);
        $this->service = app(MosqueDisplayService::class);
    }

    private function admin(bool $permission = true): User
    {
        Role::findOrCreate('admin_media_it', 'web');
        Permission::findOrCreate('manage_mosque_display', 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin_media_it');
        $user->forceFill(['two_factor_secret' => encrypt('secret'), 'two_factor_confirmed_at' => now()])->save();
        if ($permission) {
            $user->givePermissionTo('manage_mosque_display');
        }
        $this->actingAs($user)->withSession(['two_factor_verified' => true]);

        return $user;
    }

    public function test_missing_and_disabled_modules_fail_closed(): void
    {
        SystemModule::where('slug', 'mosque_display')->delete();
        $this->get('/display-masjid')->assertNotFound();
        SystemModule::create(['slug' => 'mosque_display', 'name' => 'Display', 'is_active' => false]);
        $this->get('/display-masjid/feed')->assertNotFound();
    }

    public function test_guest_and_role_without_permission_cannot_manage_display(): void
    {
        $this->get('/admin/media-it/mosque-display')->assertRedirect('/login');
        $this->admin(false);
        $this->get('/admin/media-it/mosque-display')->assertForbidden();
    }

    public function test_draft_does_not_leak_and_publish_replaces_whole_snapshot(): void
    {
        $profile = $this->service->profile();
        $profile->update(['draft' => array_replace($profile->draft, ['title' => 'Published', 'announcement' => '<script>alert(1)</script>'])]);
        $this->service->publish();
        $profile->refresh()->update(['draft' => array_replace($profile->draft, ['title' => 'Secret draft'])]);
        $response = $this->get('/display-masjid/feed')->assertOk()->assertJsonPath('title', 'Published')->assertJsonPath('version', 1);
        $response->assertDontSee('Secret draft');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->service->publish();
        $this->get('/display-masjid/feed')->assertJsonPath('title', 'Secret draft')->assertJsonPath('version', 2);
        $this->assertArrayNotHasKey('created_by', $response->json());
        $this->assertArrayNotHasKey('users', $response->json());
        $this->get('/display-masjid')->assertOk()->assertDontSee('layouts.admin')->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_youtube_urls_validate_exact_host_and_video_id(): void
    {
        foreach (['https://youtu.be/dQw4w9WgXcQ', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://youtube.com/shorts/dQw4w9WgXcQ'] as $url) {
            $this->assertSame('dQw4w9WgXcQ', MosqueDisplayService::youtubeId($url));
        }
        foreach (['https://youtube.com.evil.test/watch?v=dQw4w9WgXc', 'javascript:alert(1)', 'http://youtube.com/watch?v=dQw4w9WgXc', 'https://user@youtube.com/watch?v=dQw4w9WgXc', 'https://youtube.com/watch?v[]=dQw4w9WgXc', 'https://youtu.be/short'] as $url) {
            $this->assertNull(MosqueDisplayService::youtubeId($url));
        }
    }

    public function test_photo_is_private_until_published_and_retained_while_published(): void
    {
        $profile = $this->service->profile();
        $photo = $this->service->storePhoto(UploadedFile::fake()->image('photo.png', 100, 100));
        $item = $profile->items()->create(['type' => 'photo', 'title' => 'Photo', 'photo' => $photo, 'duration_seconds' => 5]);
        $this->get('/display-masjid/photos/'.$photo)->assertNotFound();
        $this->service->publish();
        $this->get('/display-masjid/photos/'.$photo)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->service->locked(function ($profile) use ($item, $photo) {
            $item->delete();
            $this->service->deleteUnusedPhoto($profile, $photo);
        });
        Storage::disk('local')->assertExists('mosque-display/'.$photo);
        $this->get('/display-masjid/feed')->assertJsonCount(1, 'items');
        $this->service->publish();
        Storage::disk('local')->assertMissing('mosque-display/'.$photo);
        $this->get('/display-masjid/photos/'.$photo)->assertNotFound();
    }

    public function test_missing_photo_blocks_atomic_publish_and_feed_skips_missing_media(): void
    {
        $profile = $this->service->profile();
        $this->service->publish();
        $profile->items()->create(['type' => 'photo', 'title' => 'Missing', 'photo' => 'missing.jpg', 'duration_seconds' => 5]);
        try {
            $this->service->publish();
            $this->fail('Missing media must reject publication');
        } catch (ValidationException $exception) {
            $this->assertSame(1, MosqueDisplay::find(1)->version);
            $this->get('/display-masjid/feed')->assertJsonCount(0, 'items');
        }
    }

    public function test_prayer_location_timezone_selects_new_date_without_yesterday_fallback(): void
    {
        $this->travelTo(Carbon::parse('2026-10-30 16:30:00', 'UTC'));
        $location = PrayerLocation::create(['province_id' => 1, 'city_id' => 1, 'province_name' => 'Papua', 'city_name' => 'Jayapura']);
        $times = ['dzuhur' => '12:00', 'ashar' => '15:00', 'maghrib' => '18:00', 'isya' => '19:00'];
        PrayerTime::create(['location_id' => $location->id, 'date' => '2026-10-30', 'subuh' => '04:00'] + $times);
        $profile = $this->service->profile();
        $profile->update(['draft' => array_replace($profile->draft, ['prayer_location_id' => $location->id])]);
        $this->service->publish();
        $this->get('/display-masjid/feed')->assertJsonPath('timezone', 'Asia/Jayapura')->assertJsonPath('prayer_date', '2026-10-31')->assertJsonCount(0, 'prayers');
        PrayerTime::create(['location_id' => $location->id, 'date' => '2026-10-31', 'subuh' => '04:15'] + $times);
        $this->get('/display-masjid/feed')->assertJsonPath('prayers.0.time', '04:15');
    }

    public function test_scheduled_playlist_uses_half_open_interval_and_stable_order(): void
    {
        $this->travelTo(now()->startOfMinute());
        $profile = $this->service->profile();
        foreach ([['Future', now()->addMinute(), null, 0], ['Expired', null, now(), 0], ['Second', now(), now()->addMinute(), 2], ['First', null, null, 1]] as [$title, $start, $end, $order]) {
            $profile->items()->create(['type' => 'youtube', 'title' => $title, 'youtube_id' => 'dQw4w9WgXc', 'duration_seconds' => 5, 'sort_order' => $order, 'starts_at' => $start, 'ends_at' => $end]);
        }
        $this->service->publish();
        $this->get('/display-masjid/feed')->assertJsonCount(2, 'items')->assertJsonPath('items.0.title', 'First')->assertJsonPath('items.1.title', 'Second')->assertJsonPath('items.1.scheduled', true);
    }

    public function test_admin_forms_render_and_reject_unsafe_media_and_settings(): void
    {
        $this->admin();
        $this->get('/admin/media-it/mosque-display')->assertOk()->assertSee('Pengaturan TV')->assertSee('Tata Letak')->assertSee('Salin URL')->assertSee('Buka TV / Fullscreen');
        $this->get('/admin/media-it/mosque-display/preview')->assertOk()->assertSee('Pratinjau draft');
        $this->get('/admin/media-it/mosque-display/preview-feed')->assertOk();
        $this->put('/admin/media-it/mosque-display', ['title' => 'X', 'theme' => 'evil', 'accent' => 'red;display:none'])->assertSessionHasErrors(['theme', 'accent']);
        $this->put('/admin/media-it/mosque-display', ['title' => 'X', 'theme' => 'emerald', 'accent' => '#112233', 'layout' => 'evil'])->assertSessionHasErrors('layout');
        $base = ['type' => 'youtube', 'title' => 'Video', 'sort_order' => 0, 'duration_seconds' => 4, 'youtube_url' => 'https://evil.test/video'];
        $this->post('/admin/media-it/mosque-display/items', $base)->assertSessionHasErrors('duration_seconds');
        $this->post('/admin/media-it/mosque-display/items', array_replace($base, ['duration_seconds' => 5]))->assertSessionHasErrors('youtube_url');
        $this->post('/admin/media-it/mosque-display/items', array_replace($base, ['type' => 'photo', 'duration_seconds' => 5, 'photo' => UploadedFile::fake()->create('evil.svg', 1, 'image/svg+xml')]))->assertSessionHasErrors('photo');
        $this->post('/admin/media-it/mosque-display/items', array_replace($base, ['type' => 'photo', 'duration_seconds' => 5, 'photo' => UploadedFile::fake()->image('big.jpg')->size(5121)]))->assertSessionHasErrors('photo');
    }

    public function test_authorized_draft_workflow_and_photo_preview_remain_separate_from_publication(): void
    {
        $this->admin();
        $this->put('/admin/media-it/mosque-display', [
            'title' => '<img src=x onerror=alert(1)>', 'theme' => 'midnight', 'accent' => '#112233',
            'announcement' => '<script>alert(1)</script>', 'show_activities' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->post('/admin/media-it/mosque-display/items', [
            'type' => 'photo', 'title' => 'Photo', 'sort_order' => 0, 'duration_seconds' => 5,
            'photo' => UploadedFile::fake()->image('safe.png', 200, 200), 'is_active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $item = $this->service->profile()->items()->firstOrFail();
        $this->get('/admin/media-it/mosque-display/photos/'.$item->photo)->assertOk();
        $this->get('/display-masjid/photos/'.$item->photo)->assertNotFound();
        $this->get('/admin/media-it/mosque-display')->assertOk()->assertDontSee('<script>alert(1)</script>', false);
        $this->get('/admin/media-it/mosque-display/preview-feed')->assertJsonPath('items.0.title', 'Photo');
        $this->post('/admin/media-it/mosque-display/publish')->assertSessionHasNoErrors();
        $this->put('/admin/media-it/mosque-display/items/'.$item->id, [
            'title' => 'Changed', 'sort_order' => 1, 'duration_seconds' => 300, 'is_active' => 1,
            'starts_at' => '', 'ends_at' => '',
        ])->assertSessionHasNoErrors();
        $this->get('/display-masjid/feed')->assertJsonPath('items.0.title', 'Photo');
        $this->delete('/admin/media-it/mosque-display/items/'.$item->id)->assertRedirect();
        $this->get('/display-masjid/photos/'.$item->photo)->assertOk();
        $this->post('/admin/media-it/mosque-display/publish')->assertSessionHasNoErrors();
        $this->get('/display-masjid/feed')->assertJsonCount(0, 'items');
    }

    public function test_image_pixel_bound_rejects_large_decoded_image(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->storePhoto(UploadedFile::fake()->image('wide.jpg', 4097, 2));
    }

    public function test_admin_requires_two_factor_even_with_permission(): void
    {
        $user = $this->admin();
        $user->forceFill(['two_factor_secret' => null, 'two_factor_confirmed_at' => null])->save();
        $this->get('/admin/media-it/mosque-display')->assertRedirect(route('two-factor.setup'));
    }

    public function test_branding_logo_and_layout_are_snapshot_isolated_and_publicly_whitelisted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/logo.png', 'public branding raster');
        $logo = Setting::updateOrCreate(['key' => 'logo_path'], ['group' => 'branding', 'value' => 'branding/logo.png']);
        $this->admin();
        $this->put('/admin/media-it/mosque-display', [
            'title' => 'Masjid TV', 'theme' => 'midnight', 'accent' => '#112233', 'layout' => 'split',
        ])->assertSessionHasNoErrors();
        $this->service->publish();
        $this->get('/display-masjid/feed')->assertJsonPath('logo_url', '/storage/branding/logo.png')->assertJsonPath('layout', 'split');
        $logo->update(['value' => 'https://external.test/logo.png']);
        $this->put('/admin/media-it/mosque-display', [
            'title' => 'New draft', 'theme' => 'emerald', 'accent' => '#112233', 'layout' => 'focus',
        ])->assertSessionHasNoErrors();
        $this->get('/display-masjid/feed')->assertJsonPath('logo_url', '/storage/branding/logo.png')->assertJsonPath('layout', 'split');
        $this->get('/admin/media-it/mosque-display/preview-feed')->assertJsonPath('logo_url', '/images/logo-masjidnusa.png')->assertJsonPath('layout', 'focus');
        foreach (['../private.png', 'branding/../private.png', 'branding/logo.svg', '//external.test/logo.png', 'branding/missing.png'] as $path) {
            $logo->update(['value' => $path]);
            $this->assertSame('/images/logo-masjidnusa.png', $this->service->logoUrl());
        }
        Storage::disk('public')->delete('branding/logo.png');
        $this->get('/display-masjid/feed')->assertJsonPath('logo_url', '/images/logo-masjidnusa.png');
    }

    public function test_old_snapshots_default_to_focus_and_logo_without_exposing_new_branding(): void
    {
        $profile = $this->service->profile();
        $snapshot = $this->service->snapshot($profile);
        unset($snapshot['layout'], $snapshot['logo_url']);
        $feed = $this->service->feed($snapshot, 1, null);
        $this->assertSame('focus', $feed['layout']);
        $this->assertSame('/images/logo-masjidnusa.png', $feed['logo_url']);
        $this->get('/display-masjid')->assertOk()->assertSee('id="logo"', false)->assertSee('id="fullscreen"', false)->assertSee('<footer>', false);
    }

    public function test_prayer_modes_and_running_text_settings_are_validated_and_snapshot_isolated(): void
    {
        $this->admin();
        $base = ['title' => 'TV Salat', 'theme' => 'ocean', 'accent' => '#112233', 'prayer_mode_enabled' => 1,
            'adhan_minutes' => 4, 'iqamah_minutes' => 8, 'prayer_minutes' => 12, 'announcement_seconds' => 50,
            'announcement_size' => 'extra', 'transition_ms' => 1000, 'decoration' => 'clouds'];
        $this->put('/admin/media-it/mosque-display', $base)->assertSessionHasNoErrors();
        $this->service->publish();
        $this->get('/display-masjid/feed')->assertJsonPath('prayer_mode_enabled', true)->assertJsonPath('adhan_minutes', 4)
            ->assertJsonPath('iqamah_minutes', 8)->assertJsonPath('prayer_minutes', 12)->assertJsonPath('theme', 'ocean')
            ->assertJsonPath('announcement_seconds', 50)->assertJsonPath('transition_ms', 1000)->assertJsonPath('decoration', 'clouds');
        $this->put('/admin/media-it/mosque-display', array_replace($base, ['prayer_mode_enabled' => 0, 'iqamah_minutes' => 0]))->assertSessionHasNoErrors();
        $this->get('/display-masjid/feed')->assertJsonPath('prayer_mode_enabled', true)->assertJsonPath('iqamah_minutes', 8);
        foreach (['adhan_minutes' => 0, 'iqamah_minutes' => 31, 'prayer_minutes' => 61, 'announcement_seconds' => 1, 'announcement_size' => 'huge', 'transition_ms' => 5000, 'decoration' => 'script'] as $field => $value) {
            $this->put('/admin/media-it/mosque-display', array_replace($base, [$field => $value]))->assertSessionHasErrors($field);
        }
    }

    public function test_footer_keeps_extra_times_but_prayer_timeline_only_uses_five_prayers(): void
    {
        $this->travelTo(Carbon::parse('2026-10-31 00:05:00', 'Asia/Jakarta'));
        $location = PrayerLocation::create(['province_id' => 1, 'city_id' => 1, 'province_name' => 'Jakarta', 'city_name' => 'Jakarta']);
        foreach (['2026-10-30', '2026-10-31', '2026-11-01'] as $date) {
            PrayerTime::create(['location_id' => $location->id, 'date' => $date, 'imsak' => '04:00', 'subuh' => '04:10', 'terbit' => '05:30', 'dhuha' => '06:00', 'dzuhur' => '12:00', 'ashar' => '15:00', 'maghrib' => '18:00', 'isya' => '23:55']);
        }
        $profile = $this->service->profile();
        $profile->update(['draft' => array_replace($profile->draft, ['prayer_location_id' => $location->id])]);
        $this->service->publish();
        $this->get('/display-masjid/feed')->assertJsonCount(7, 'prayers')->assertJsonPath('prayers.0.key', 'imsak')
            ->assertJsonPath('prayers.1.key', 'subuh')->assertJsonPath('prayers.2.key', 'terbit')->assertJsonPath('prayers.6.key', 'isya')
            ->assertJsonPath('next_prayer.key', 'subuh')
            ->assertJsonPath('previous_prayer.at', '2026-10-30T23:55:00+07:00');
    }

    public function test_manual_dated_prayer_overrides_recurring_without_duplicate_footer_items(): void
    {
        $this->travelTo(Carbon::parse('2026-10-31 11:00:00', 'Asia/Jakarta'));
        PrayerSchedule::create(['prayer_name' => 'Dzuhur', 'time' => '12:00', 'type' => 'daily', 'is_active' => true]);
        PrayerSchedule::create(['prayer_name' => 'Dzuhur', 'time' => '12:15', 'date' => '2026-10-31', 'type' => 'daily', 'is_active' => true]);
        PrayerSchedule::create(['prayer_name' => 'Imsak', 'time' => '04:00', 'type' => 'daily', 'is_active' => true]);
        PrayerSchedule::create(['prayer_name' => 'Terbit', 'time' => '05:30', 'type' => 'daily', 'is_active' => true]);
        $this->service->publish();
        $this->get('/display-masjid/feed')->assertJsonCount(3, 'prayers')->assertJsonPath('prayers.0.key', 'imsak')
            ->assertJsonPath('prayers.1.key', 'terbit')->assertJsonPath('prayers.2.key', 'dzuhur')->assertJsonPath('prayers.2.time', '12:15');
    }

    public function test_older_settings_submission_preserves_new_prayer_and_ticker_preferences(): void
    {
        $this->admin();
        $profile = $this->service->profile();
        $profile->update(['draft' => array_replace($profile->draft, ['prayer_mode_enabled' => true, 'iqamah_minutes' => 7, 'announcement_seconds' => 60])]);
        $this->put('/admin/media-it/mosque-display', ['title' => 'Edit judul', 'theme' => 'emerald', 'accent' => '#112233'])->assertSessionHasNoErrors();
        $draft = $profile->fresh()->draft;
        $this->assertTrue($draft['prayer_mode_enabled']);
        $this->assertSame(7, $draft['iqamah_minutes']);
        $this->assertSame(60, $draft['announcement_seconds']);
    }

    public function test_old_published_snapshot_does_not_enable_prayer_automation_without_opt_in(): void
    {
        $profile = $this->service->profile();
        $snapshot = $this->service->snapshot($profile);
        foreach (['prayer_mode_enabled', 'adhan_minutes', 'iqamah_minutes', 'prayer_minutes', 'announcement_seconds', 'announcement_size', 'transition_ms', 'decoration'] as $key) {
            unset($snapshot[$key]);
        }
        $feed = $this->service->feed($snapshot, 1, null);
        $this->assertFalse($feed['prayer_mode_enabled']);
        $this->assertSame(3, $feed['adhan_minutes']);
        $this->assertSame(10, $feed['iqamah_minutes']);
        $this->assertSame(15, $feed['prayer_minutes']);
        $this->assertSame(40, $feed['announcement_seconds']);
    }

    public function test_full_screen_media_and_fit_settings_preserve_draft_publication_boundary(): void
    {
        $this->admin();
        $this->service->publish();
        $this->put('/admin/media-it/mosque-display', ['title' => 'TV Penuh', 'theme' => 'emerald', 'accent' => '#112233', 'layout' => 'immersive', 'media_fit' => 'cover'])->assertSessionHasNoErrors();
        $this->get('/display-masjid/feed')->assertJsonPath('layout', 'focus')->assertJsonPath('media_fit', 'contain');
        $this->get('/admin/media-it/mosque-display/preview-feed')->assertJsonPath('layout', 'immersive')->assertJsonPath('media_fit', 'cover');
        $this->service->publish();
        $this->get('/display-masjid/feed')->assertJsonPath('layout', 'immersive')->assertJsonPath('media_fit', 'cover');
        $this->put('/admin/media-it/mosque-display', ['title' => 'TV', 'theme' => 'emerald', 'accent' => '#112233', 'media_fit' => 'stretch'])->assertSessionHasErrors('media_fit');
    }

    public function test_saved_settings_return_to_selected_tab_without_persisting_ui_state(): void
    {
        $this->admin();
        foreach (['identity', 'layout', 'content', 'prayer'] as $tab) {
            $this->put('/admin/media-it/mosque-display', ['title' => 'TV', 'theme' => 'emerald', 'accent' => '#112233', '_tab' => $tab])
                ->assertRedirect(route('admin.media-it.mosque-display.index').'#'.$tab)->assertSessionHasNoErrors();
        }
        $this->assertArrayNotHasKey('_tab', $this->service->profile()->draft);
        $this->from(route('admin.media-it.mosque-display.index'))->post('/admin/media-it/mosque-display/items', [
            '_tab' => 'media', 'type' => 'youtube', 'title' => 'Video', 'sort_order' => 0, 'duration_seconds' => 20, 'youtube_url' => 'bad',
        ])->assertSessionHasErrors('youtube_url')->assertSessionHasInput('_tab', 'media');
        $this->post('/admin/media-it/mosque-display/publish')->assertRedirect(route('admin.media-it.mosque-display.index').'#publish');
    }

    public function test_prayer_simulation_is_only_available_in_authorized_admin_preview(): void
    {
        $this->get('/admin/media-it/mosque-display/preview?phase=adhan')->assertRedirect(route('login'));
        $this->admin();
        foreach (['adhan', 'iqamah', 'prayer'] as $phase) {
            $this->get('/admin/media-it/mosque-display/preview?phase='.$phase)->assertOk()->assertViewHas('previewPhase', $phase)->assertSee('data-preview-phase="'.$phase.'"', false);
        }
        $this->get('/admin/media-it/mosque-display/preview?phase=bad')->assertSessionHasErrors('phase');
        $this->get('/display-masjid?phase=adhan')->assertOk()->assertViewMissing('previewPhase')->assertSee('data-preview-phase=""', false)->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_seasonal_scenery_is_whitelisted_and_keeps_schedule_and_publication_isolated(): void
    {
        $this->admin();
        $this->service->publish();
        foreach (array_keys(MosqueDisplayService::DECORATIONS) as $decoration) {
            $this->put('/admin/media-it/mosque-display', ['title' => 'TV', 'theme' => 'emerald', 'accent' => '#112233', 'decoration' => $decoration, '_tab' => 'layout'])
                ->assertSessionHasNoErrors()->assertRedirect(route('admin.media-it.mosque-display.index').'#layout');
            $this->get('/admin/media-it/mosque-display/preview-feed')->assertJsonPath('decoration', $decoration)->assertJsonPath('prayer_mode_enabled', false);
        }
        $this->get('/display-masjid/feed')->assertJsonPath('decoration', 'mosque');
        $this->put('/admin/media-it/mosque-display', ['title' => 'TV', 'theme' => 'emerald', 'accent' => '#112233', 'decoration' => '<script>'])->assertSessionHasErrors('decoration');
    }
}
