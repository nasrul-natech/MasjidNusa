<?php

namespace App\Http\Middleware;

use App\Support\RememberLogin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtendRememberedSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $until = $request->hasSession() ? $request->session()->get('remember_until') : null;

        if (is_numeric($until) && (int) $until > now()->timestamp) {
            $minutes = (int) ceil(((int) $until - now()->timestamp) / 60);
            config(['session.lifetime' => min(RememberLogin::MINUTES, max(1, $minutes))]);
        }

        return $next($request);
    }
}
