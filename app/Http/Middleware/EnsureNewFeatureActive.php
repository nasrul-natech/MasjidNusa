<?php

namespace App\Http\Middleware;

use App\Support\NewFeature;
use Closure;
use Illuminate\Http\Request;

class EnsureNewFeatureActive
{
    public function handle(Request $request, Closure $next, string $slug)
    {
        abort_unless(NewFeature::enabled($slug), 404);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
