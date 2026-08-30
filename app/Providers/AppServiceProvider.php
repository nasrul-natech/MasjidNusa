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
    }
}
