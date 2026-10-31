<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MosqueDisplay;
use App\Services\MosqueDisplayService;
use Illuminate\Support\Facades\Storage;

class MosqueDisplayPublicController extends Controller
{
    public function index()
    {
        return response()->view('mosque-display.screen', ['preview' => false, 'feedUrl' => route('mosque-display.feed')])
            ->header('Cache-Control', 'no-store, public')->header('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function feed(MosqueDisplayService $display)
    {
        $profile = MosqueDisplay::find(1);
        abort_unless($profile?->published, 404);

        return response()->json($display->feed($profile->published, $profile->version, $profile->published_at->toIso8601String()))->header('Cache-Control', 'no-store, public');
    }

    public function photo(string $photo)
    {
        $profile = MosqueDisplay::find(1);
        abort_unless(in_array($photo, array_column($profile?->published['items'] ?? [], 'photo'), true), 404);
        abort_unless(Storage::disk('local')->exists('mosque-display/'.$photo), 404);

        return response()->file(Storage::disk('local')->path('mosque-display/'.$photo), ['Cache-Control' => 'no-store, public', 'Content-Type' => 'image/jpeg', 'X-Content-Type-Options' => 'nosniff']);
    }
}
