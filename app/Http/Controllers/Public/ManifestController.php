<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\BrandingService;

class ManifestController extends Controller
{
    public function __invoke(BrandingService $brand)
    {
        return response()->json([
            'name'             => $brand->appName(),
            'short_name'       => $brand->appName(),
            'description'      => $brand->tagline(),
            'start_url'        => '.',
            'scope'            => '.',
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#ffffff',
            'theme_color'      => $brand->primaryColor(),
            'lang'             => 'id',
            'categories'       => ['religion', 'social'],
            'icons'            => [
                ['src' => 'images/icons/icon-72.png', 'sizes' => '72x72', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => 'images/icons/icon-96.png', 'sizes' => '96x96', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => 'images/icons/icon-128.png', 'sizes' => '128x128', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => 'images/icons/icon-144.png', 'sizes' => '144x144', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => 'images/icons/icon-152.png', 'sizes' => '152x152', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => 'images/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => 'images/icons/icon-384.png', 'sizes' => '384x384', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => 'images/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
            'shortcuts' => [
                ['name' => 'Donasi', 'short_name' => 'Donasi', 'url' => 'donasi', 'icons' => [['src' => 'images/icons/icon-96.png', 'sizes' => '96x96']]],
                ['name' => 'Jadwal Sholat', 'short_name' => 'Jadwal', 'url' => 'jadwal-sholat', 'icons' => [['src' => 'images/icons/icon-96.png', 'sizes' => '96x96']]],
            ],
            'prefer_related_applications' => false,
        ])->header('Content-Type', 'application/manifest+json');
    }
}
