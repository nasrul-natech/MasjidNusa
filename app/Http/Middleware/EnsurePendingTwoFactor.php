<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePendingTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $pendingId = $request->session()->get('login.id');
        if ($pendingId) {
            $pending = User::find($pendingId);
            if ($pending && $pending->requiresTwoFactor()) {
                return $next($request);
            }

            return redirect()->route('login');
        }

        $user = $request->user();
        if ($user && $user->requiresTwoFactor() && !$request->session()->get('two_factor_verified')) {
            return $next($request);
        }

        if ($user) {
            return redirect($user->isJamaah() ? route('home') : $user->getAdminPanelUrl());
        }

        return redirect()->route('login');
    }
}
