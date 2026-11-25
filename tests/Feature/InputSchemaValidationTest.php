<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureModuleActive;
use App\Http\Middleware\EnsureTwoFactorVerified;
use App\Models\BankAccount;
use App\Models\Banner;
use App\Models\DonationCampaign;
use App\Models\PopupAd;
use App\Models\PrayerLocation;
use App\Models\PrayerSchedule;
use App\Models\PrayerTime;
use App\Models\User;
use App\Services\PrayerScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Tests\TestCase;

class InputSchemaValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Isolate input validation from admin authorization and feature flags.
        $this->withoutMiddleware([
            CheckRole::class,
            EnsureModuleActive::class,
            EnsureTwoFactorVerified::class,
            PermissionMiddleware::class,
        ]);
        $this->actingAs(User::factory()->create(['is_active' => true]));
    }

    public function test_profile_address_matches_schema_limit(): void
    {
        $data = ['name' => 'Jamaah', 'phone' => null, 'gender' => null, 'birth_date' => null];

        $this->putJson(route('user.profile.update'), $data + ['address' => str_repeat('a', 256)])
            ->assertUnprocessable()->assertJsonValidationErrors('address');
        $this->assertNull(auth()->user()->fresh()->address);

        $this->putJson(route('user.profile.update'), $data + ['address' => str_repeat('a', 255)])
            ->assertRedirect();
        $this->assertSame(str_repeat('a', 255), auth()->user()->fresh()->address);
    }

    public function test_bank_update_uses_create_length_limits(): void
    {
        $account = BankAccount::create([
            'bank_name' => 'Bank', 'account_name' => 'Masjid', 'account_number' => '123',
        ]);
        $data = [
            'bank_name' => str_repeat('b', 100),
            'account_name' => str_repeat('a', 255),
            'account_number' => str_repeat('1', 50),
        ];

        foreach ($data as $field => $value) {
            $this->putJson(route('admin.keuangan.bank-accounts.update', $account), array_replace($data, [$field => $value.'x']))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertSame('123', $account->fresh()->account_number);

        $this->putJson(route('admin.keuangan.bank-accounts.update', $account), $data)->assertRedirect();
        $this->assertSame($data, $account->fresh()->only(array_keys($data)));
    }

    public static function contentLimits(): array
    {
        return [
            'banner' => [Banner::class, 'admin.media-it.banners', 'link', ['title' => 'Banner', 'display_type' => 'all'], ['image' => 'existing.png']],
            'popup' => [PopupAd::class, 'admin.media-it.popup-ads', 'link', ['title' => 'Popup', 'ad_type' => 'link', 'duration' => 5], ['image' => 'existing.png']],
            'prayer notes' => [PrayerSchedule::class, 'admin.operasional.prayer-schedule', 'notes', ['prayer_name' => 'Subuh', 'time' => '04:30', 'type' => 'daily'], []],
        ];
    }

    #[DataProvider('contentLimits')]
    public function test_content_limits_apply_to_create_and_update(string $model, string $route, string $field, array $data, array $stored): void
    {
        $record = $model::create($data + $stored + [$field => 'Existing']);

        $this->postJson(route($route.'.store'), $data + [$field => str_repeat('x', 256)])
            ->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->putJson(route($route.'.update', $record), $data + [$field => str_repeat('x', 256)])
            ->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertSame('Existing', $record->fresh()->getAttribute($field));
        $this->assertSame(1, $model::count());

        $this->putJson(route($route.'.update', $record), $data + [$field => str_repeat('x', 255)])
            ->assertRedirect();
        $this->assertSame(str_repeat('x', 255), $record->fresh()->getAttribute($field));
    }

    public static function campaignTitles(): array
    {
        return ['short' => ['Infak Masjid'], 'maximum' => [str_repeat('a', 255)], 'expanded transliteration' => [str_repeat("\u{6350}", 255)]];
    }

    #[DataProvider('campaignTitles')]
    public function test_campaign_slug_keeps_timestamp_within_schema_limit(string $title): void
    {
        $before = time();
        $this->postJson(route('admin.keuangan.campaigns.store'), [
            'title' => $title, 'target_amount' => 10000, 'status' => 'draft',
        ])->assertRedirect();

        $slug = DonationCampaign::sole()->slug;
        $suffix = substr($slug, strrpos($slug, '-'));
        $this->assertLessThanOrEqual(255, strlen($slug));
        $this->assertGreaterThanOrEqual($before, (int) substr($suffix, 1));
        $this->assertLessThanOrEqual(time(), (int) substr($suffix, 1));
        $this->assertSame(substr(Str::slug($title), 0, 255 - strlen($suffix)).$suffix, $slug);
    }

    public static function requiredPrayerTimes(): array
    {
        $cases = [];
        foreach (['subuh' => 'Fajr', 'dzuhur' => 'Dhuhr', 'ashar' => 'Asr', 'maghrib' => 'Maghrib', 'isya' => 'Isha'] as $prayer => $apiKey) {
            foreach ([null, '', '24:00', '12:60', '04:30junk', ['04:30']] as $index => $invalid) {
                $cases[$prayer.'-'.$index] = [$prayer, $apiKey, $invalid];
            }
        }

        return $cases;
    }

    #[DataProvider('requiredPrayerTimes')]
    public function test_api_skips_invalid_required_times_without_overwriting_existing_day(string $prayer, string $apiKey, mixed $invalid): void
    {
        $location = $this->location();
        $existing = PrayerTime::create(['location_id' => $location->id, 'date' => '2026-11-01'] + $this->times());
        $timings = ['Fajr' => '04:30 (WIB)', 'Dhuhr' => '12:00 (WIB)', 'Asr' => '15:00 (WIB)', 'Maghrib' => '18:00 (WIB)', 'Isha' => '19:00 (WIB)'];
        $bad = array_replace($timings, [$apiKey => $invalid]);
        Http::preventStrayRequests();
        Http::fake(['api.aladhan.com/*' => Http::response(['data' => [
            ['date' => ['gregorian' => ['date' => '01-11-2026']], 'timings' => $bad],
            ['date' => ['gregorian' => ['date' => '02-11-2026']], 'timings' => $bad],
            ['date' => ['gregorian' => ['date' => '03-11-2026']], 'timings' => $timings],
        ]])]);

        $this->assertSame(1, app(PrayerScheduleService::class)->fetchAndSave($location, 11, 2026));
        $this->assertSame($this->times(), $existing->fresh()->only(array_keys($this->times())));
        $this->assertSame(2, PrayerTime::count());
        $this->assertFalse(PrayerTime::whereDate('date', '2026-11-02')->exists());
        $this->assertSame($this->times(), PrayerTime::whereDate('date', '2026-11-03')->sole()->only(array_keys($this->times())));
    }

    public function test_csv_skips_incomplete_or_invalid_rows_and_keeps_valid_rows(): void
    {
        $location = $this->location();
        $existing = PrayerTime::create(['location_id' => $location->id, 'date' => '2026-11-01'] + $this->times());
        $rows = ['Tanggal;Subuh;Dzuhur;Ashar;Maghrib;Isya'];
        $day = 1;
        foreach (array_keys($this->times()) as $prayer) {
            foreach (['', '24:00', '12:60', '04:30junk'] as $invalid) {
                $times = array_replace($this->times(), [$prayer => $invalid]);
                $rows[] = $day++.';'.implode(';', $times);
            }
        }
        $rows[] = '21;04:30;12:00;15:00;18:00;19:00';
        $rows[] = '22;04:30;12:00;15:00;18:00';

        $this->post(route('admin.operasional.prayer-schedule.import'), [
            'location_id' => $location->id, 'month' => 11, 'year' => 2026,
            'csv_file' => UploadedFile::fake()->createWithContent('schedule.csv', implode("\n", $rows)),
        ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionMissing('error');

        $this->assertSame(2, PrayerTime::count());
        $this->assertSame($this->times(), $existing->fresh()->only(array_keys($this->times())));
        $this->assertSame($this->times(), PrayerTime::whereDate('date', '2026-11-21')->sole()->only(array_keys($this->times())));
    }

    public function test_csv_requires_all_required_prayer_columns(): void
    {
        $location = $this->location();
        $this->post(route('admin.operasional.prayer-schedule.import'), [
            'location_id' => $location->id, 'month' => 11, 'year' => 2026,
            'csv_file' => UploadedFile::fake()->createWithContent('schedule.csv', "Tanggal;Subuh;Dzuhur\n1;04:30;12:00"),
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('prayer_times', 0);
    }

    private function location(): PrayerLocation
    {
        return PrayerLocation::create([
            'province_id' => '1', 'city_id' => '1', 'province_name' => 'Jakarta',
            'city_name' => 'Jakarta', 'latitude' => -6.2, 'longitude' => 106.8,
        ]);
    }

    private function times(): array
    {
        return ['subuh' => '04:30', 'dzuhur' => '12:00', 'ashar' => '15:00', 'maghrib' => '18:00', 'isya' => '19:00'];
    }
}
