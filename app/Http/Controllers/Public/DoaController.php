<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Doa;
use App\Models\DoaCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoaController extends Controller
{
    public function index(Request $request)
    {
        $categories = DoaCategory::withCount('publishedDuas')
            ->orderBy('sort_order')
            ->get();

        $tab = $request->get('tab', 'kategori');
        $categorySlug = $request->get('category');

        $favoritedIds = [];
        if (Auth::check()) {
            $favoritedIds = Auth::user()->favoriteDuas()->pluck('doa_id')->toArray();
        }

        $duas = collect();

        if ($tab === 'favorites') {
            if (Auth::check()) {
                $duas = Doa::published()
                    ->whereIn('id', $favoritedIds)
                    ->with('category')
                    ->orderBy('title')
                    ->get();
            }
        } elseif ($categorySlug) {
            $duas = Doa::published()
                ->with('category')
                ->whereHas('category', fn($q) => $q->where('slug', $categorySlug))
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get();
        }

        return view('public.doa.index', compact('categories', 'duas', 'tab', 'categorySlug', 'favoritedIds'));
    }

    public function show(string $slug)
    {
        $doa = Doa::where('slug', $slug)->published()->with('category')->firstOrFail();

        $isFavorited = Auth::check()
            ? Auth::user()->favoriteDuas()->where('doa_id', $doa->id)->exists()
            : false;

        $related = Doa::published()
            ->where('id', '!=', $doa->id)
            ->where('doa_category_id', $doa->doa_category_id)
            ->limit(5)
            ->get();

        return view('public.doa.show', compact('doa', 'isFavorited', 'related'));
    }

    public function toggleFavorite(Doa $doa)
    {
        $user = Auth::user();
        $exists = $user->favoriteDuas()->where('doa_id', $doa->id)->exists();

        if ($exists) {
            $user->favoriteDuas()->detach($doa->id);
            $favorited = false;
        } else {
            $user->favoriteDuas()->attach($doa->id);
            $favorited = true;
        }

        return response()->json(['favorited' => $favorited]);
    }
}
