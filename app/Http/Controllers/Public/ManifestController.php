<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\BrandingService;

class ManifestController extends Controller
{
    public function __invoke(BrandingService $brand)
    {
        $icon = fn (string $file, string $size, string $purpose = 'any') => [
            'src'     => asset("images/icons/{$file}"),
            'sizes'   => $size,
            'type'    => 'image/png',
            'purpose' => $purpose,
        ];

        return response()->json([
            'id'               => '/',
            'name'             => $brand->appName(),
            'short_name'       => $brand->appName(),
            'description'      => $brand->tagline(),
            'start_url'        => '/',
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#ffffff',
            'theme_color'      => $brand->primaryColor(),
            'lang'             => 'id',
            'categories'       => ['religion', 'social'],
            'icons'            => [
                $icon('icon-72.png', '72x72'),
                $icon('icon-96.png', '96x96'),
                $icon('icon-128.png', '128x128'),
                $icon('icon-144.png', '144x144'),
                $icon('icon-152.png', '152x152'),
                $icon('icon-192.png', '192x192', 'any'),
                $icon('icon-192.png', '192x192', 'maskable'),
                $icon('icon-384.png', '384x384'),
                $icon('icon-512.png', '512x512', 'any'),
                $icon('icon-512.png', '512x512', 'maskable'),
            ],
            'shortcuts' => [
                [
                    'name'       => 'Donasi',
                    'short_name' => 'Donasi',
                    'url'        => url('/donasi'),
                    'icons'      => [$icon('icon-96.png', '96x96')],
                ],
                [
                    'name'       => 'Jadwal Sholat',
                    'short_name' => 'Jadwal',
                    'url'        => url('/jadwal-sholat'),
                    'icons'      => [$icon('icon-96.png', '96x96')],
                ],
            ],
            'prefer_related_applications' => false,
        ])->header('Content-Type', 'application/manifest+json');
    }
}
