<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\BrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BrandingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_defaults_when_settings_are_empty(): void
    {
        $brand = app(BrandingService::class);

        $this->assertSame('MasjidNusa', $brand->appName());
        $this->assertSame('Menghubungkan Masjid, Umat & Kebaikan', $brand->tagline());
        $this->assertSame('Masjid Nusa', $brand->mosqueName());
        $this->assertSame('#064e3b', $brand->primaryColor());
        $this->assertSame('#c59d3f', $brand->accentColor());
        $this->assertStringContainsString('logo-masjidnusa.png', $brand->logoUrl());
        $this->assertStringContainsString('logo-masjidnusa-icon.png', $brand->logoIconUrl());
        $this->assertStringContainsString('favicon.png', $brand->faviconUrl());
    }

    public function test_it_returns_saved_settings(): void
    {
        Setting::create(['key' => 'app_name', 'value' => 'Masjid Al-Ikhlas', 'group' => 'branding']);
        Setting::create(['key' => 'app_tagline', 'value' => 'Bersama Membangun Umat', 'group' => 'branding']);
        Setting::create(['key' => 'mosque_name', 'value' => 'Masjid Al-Ikhlas', 'group' => 'general']);
        Setting::create(['key' => 'primary_color', 'value' => '#1a365d', 'group' => 'branding']);

        BrandingService::clearCache();
        $brand = app(BrandingService::class);

        $this->assertSame('Masjid Al-Ikhlas', $brand->appName());
        $this->assertSame('Bersama Membangun Umat', $brand->tagline());
        $this->assertSame('Masjid Al-Ikhlas', $brand->mosqueName());
        $this->assertSame('#1a365d', $brand->primaryColor());
    }

    public function test_clear_cache_refreshes_branding_values(): void
    {
        Setting::create(['key' => 'app_name', 'value' => 'Awal', 'group' => 'branding']);
        BrandingService::clearCache();

        $brand = app(BrandingService::class);
        $this->assertSame('Awal', $brand->appName());

        Setting::where('key', 'app_name')->update(['value' => 'Baru']);
        Cache::forget('setting_app_name');
        BrandingService::clearCache();

        $brand = app(BrandingService::class);
        $this->assertSame('Baru', $brand->appName());
    }

    public function test_info_sistem_page_is_accessible(): void
    {
        $response = $this->get(route('info-sistem'));

        $response->assertOk();
        $response->assertSee(config('platform.copyright'));
        $response->assertSee(config('platform.developer'));
    }

    public function test_manifest_route_returns_json(): void
    {
        $response = $this->get(route('manifest'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/manifest+json');
        $response->assertJsonFragment(['name' => 'MasjidNusa']);
    }
}
