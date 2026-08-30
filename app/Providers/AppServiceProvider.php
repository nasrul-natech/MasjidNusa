<?php

namespace App\Providers;

use App\Services\BrandingService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BrandingService::class);
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('brand', app(BrandingService::class));
        });

        if (!app()->runningInConsole()) {
            $this->forceUrlFromRequest();
        }
    }

    /**
     * Sync asset/url generation with the actual request host,
     * keeping the path prefix from APP_URL so XAMPP subdirectories
     * and production root domains both resolve correctly.
     */
    private function forceUrlFromRequest(): void
    {
        $request = request();
        $scheme  = $request->getScheme();
        $host    = $request->getHttpHost();

        $parsed = parse_url(config('app.url'));
        $path   = rtrim($parsed['path'] ?? '', '/');

        $rootUrl = "{$scheme}://{$host}{$path}";

        URL::forceScheme($scheme);
        URL::forceRootUrl($rootUrl);

        config(['filesystems.disks.public.url' => rtrim($rootUrl, '/') . '/storage']);

        // #region agent log
        if (in_array($request->path(), ['', 'manifest.json', 'donasi', 'login'], true)) {
            $configUrl = rtrim((string) config('app.url'), '/');
            @file_put_contents(
                '/Users/nasrul/Herd/update/MasjidNusa/.cursor/debug-c5a801.log',
                json_encode([
                    'sessionId'    => 'c5a801',
                    'runId'        => 'http-request',
                    'hypothesisId' => 'H4,H6',
                    'location'     => 'AppServiceProvider::forceUrlFromRequest',
                    'message'      => 'URL sync on key route',
                    'data'         => [
                        'path'           => $request->path(),
                        'requestScheme'  => $scheme,
                        'requestHost'    => $host,
                        'configAppUrl'   => $configUrl,
                        'forcedRootUrl'  => $rootUrl,
                        'assetSample'    => asset('images/logo-masjidnusa.png'),
                        'baseUrlMetaWould'=> $configUrl . '/',
                        'urlMismatch'    => $configUrl !== rtrim($rootUrl, '/'),
                        'isSecure'       => $request->secure(),
                        'xForwardedProto'=> $request->header('X-Forwarded-Proto'),
                    ],
                    'timestamp' => (int) round(microtime(true) * 1000),
                ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
                FILE_APPEND
            );
        }
        // #endregion
    }
}
