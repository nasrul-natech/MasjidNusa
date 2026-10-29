<?php

namespace App\Providers;

use App\Services\BrandingService;
use App\Models\User;
use App\Support\RememberLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
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
        $guard = Auth::guard('web');
        if (method_exists($guard, 'setRememberDuration')) {
            $guard->setRememberDuration(RememberLogin::MINUTES);
        }

        View::composer('*', function ($view) {
            $view->with('brand', app(BrandingService::class));
        });

        Event::listen(function (Login $event) {
            if (!$event->remember || !$event->user instanceof User) {
                return;
            }

            $request = request();
            if (!$request->hasSession()) {
                return;
            }

            RememberLogin::persist($request, true);
            if ($event->user->requiresTwoFactor()) {
                $rememberedAt = $event->user->two_factor_remembered_at;
                if ($rememberedAt && $rememberedAt->greaterThan(now()->subDays(RememberLogin::DAYS))) {
                    $request->session()->put('two_factor_verified', true);
                }
            }
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
