<?php

namespace App\Http\Middleware;

use App\Models\SystemModule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleActive
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!SystemModule::isActive($module)) {
            abort(503, 'Modul ini sedang tidak aktif.');
        }

        return $next($request);
    }
}
