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

    public function cssVariables(): string
    {
        $primary = $this->safeHex($this->primaryColor(), self::DEFAULTS['primary_color']);
        $accent = $this->safeHex($this->accentColor(), self::DEFAULTS['accent_color']);
        $primaryScale = $this->scale($primary);
        $accentScale = $this->scale($accent);

        $lines = [
            '--color-primary: '.$primary.';',
            '--color-primary-dark: '.$primaryScale[800].';',
            '--color-primary-light: '.$primaryScale[400].';',
            '--color-accent: '.$accent.';',
            '--color-accent-dark: '.$accentScale[800].';',
        ];

        foreach ($primaryScale as $step => $value) {
            $lines[] = "--color-emerald-{$step}: {$value};";
            $lines[] = "--color-teal-{$step}: {$value};";
        }

        foreach ($accentScale as $step => $value) {
            $lines[] = "--color-amber-{$step}: {$value};";
        }

        return ":root {\n            ".implode("\n            ", $lines)."\n        }";
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

    private function safeHex(string $color, string $fallback): string
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : $fallback;
    }

    private function scale(string $hex): array
    {
        return [
            50 => "color-mix(in srgb, {$hex} 8%, white)",
            100 => "color-mix(in srgb, {$hex} 14%, white)",
            200 => "color-mix(in srgb, {$hex} 26%, white)",
            300 => "color-mix(in srgb, {$hex} 42%, white)",
            400 => "color-mix(in srgb, {$hex} 62%, white)",
            500 => "color-mix(in srgb, {$hex} 80%, white)",
            600 => "color-mix(in srgb, {$hex} 92%, white)",
            700 => $hex,
            800 => "color-mix(in srgb, {$hex} 78%, black)",
            900 => "color-mix(in srgb, {$hex} 62%, black)",
            950 => "color-mix(in srgb, {$hex} 42%, black)",
        ];
    }

    private function assetUrl(?string $path, string $fallback): string
    {
        if ($path) {
            return asset('storage/' . ltrim($path, '/'));
        }

        return asset($fallback);
    }
}
