<?php

namespace App\Services;

use App\Models\MosqueProfile;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class BrandingService
{
    private const CACHE_KEY = 'branding_all';

    private const DEFAULTS = [
        'app_name'       => 'MasjidNusa',
        'app_tagline'    => 'Menghubungkan Masjid, Umat & Kebaikan',
        'mosque_name'    => 'Masjid Nusa',
        'primary_color'  => '#064e3b',
        'accent_color'   => '#c59d3f',
        'logo_path'      => null,
        'logo_icon_path' => null,
        'favicon_path'   => null,
    ];

    private ?array $settings = null;

    public function appName(): string
    {
        return $this->get('app_name');
    }

    public function tagline(): string
    {
        return $this->get('app_tagline');
    }

    public function mosqueName(): string
    {
        $name = $this->raw('mosque_name');

        if ($name) {
            return $name;
        }

        return MosqueProfile::current()?->name ?? self::DEFAULTS['mosque_name'];
    }

    public function logoUrl(): string
    {
        return $this->assetUrl($this->raw('logo_path'), 'images/logo-masjidnusa.png');
    }

    public function logoIconUrl(): string
    {
        return $this->assetUrl($this->raw('logo_icon_path'), 'images/logo-masjidnusa-icon.png');
    }

    public function faviconUrl(): string
    {
        return $this->assetUrl($this->raw('favicon_path'), 'favicon.png');
    }

    public function primaryColor(): string
    {
        return $this->get('primary_color');
    }

    public function accentColor(): string
    {
        return $this->get('accent_color');
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);

        foreach (array_keys(self::DEFAULTS) as $key) {
            Cache::forget("setting_{$key}");
        }

        if (app()->bound(self::class)) {
            app()->forgetInstance(self::class);
        }
    }

    private function get(string $key): string
    {
        return $this->raw($key) ?? self::DEFAULTS[$key] ?? '';
    }

    private function raw(string $key): ?string
    {
        $value = $this->all()[$key] ?? null;

        return ($value !== null && $value !== '') ? $value : null;
    }

    private function all(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $this->settings = Cache::remember(self::CACHE_KEY, 3600, function () {
            $branding = Setting::where('group', 'branding')->pluck('value', 'key')->toArray();
            $general  = Setting::where('group', 'general')->where('key', 'mosque_name')->pluck('value', 'key')->toArray();

            return array_merge($branding, $general);
        });

        return $this->settings;
    }

    private function assetUrl(?string $path, string $fallback): string
    {
        if ($path) {
            return asset('storage/' . ltrim($path, '/'));
        }

        return asset($fallback);
    }
}
