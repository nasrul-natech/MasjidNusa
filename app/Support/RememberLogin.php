<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class RememberLogin
{
    public const DAYS = 30;

    public const MINUTES = 43200;

    public static function start(bool $remember): void
    {
        if (!$remember) {
            return;
        }

        $guard = Auth::guard('web');
        if (method_exists($guard, 'setRememberDuration')) {
            $guard->setRememberDuration(self::MINUTES);
        }

        config(['session.lifetime' => self::MINUTES]);
    }

    public static function persist(Request $request, bool $remember): void
    {
        if (!$remember) {
            $request->session()->forget('remember_until');

            return;
        }

        config(['session.lifetime' => self::MINUTES]);
        $request->session()->put('remember_until', now()->addMinutes(self::MINUTES)->timestamp);
    }

    public static function forget(): void
    {
        $guard = Auth::guard('web');
        if (method_exists($guard, 'getRecallerName')) {
            Cookie::queue(Cookie::forget($guard->getRecallerName()));
        }
    }
}
