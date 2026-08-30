<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category');
        $search = $request->get('q');

        $query = Article::with(['category', 'author'])->published()->orderByDesc('published_at');

        if ($category) {
            $query->whereHas('category', fn($q) => $q->where('slug', $category));
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $articles = $query->paginate(9);
        $categories = ArticleCategory::all();

        return view('public.dakwah.index', compact('articles', 'categories', 'category', 'search'));
    }

    public function show(string $slug)
    {
        $article = Article::where('slug', $slug)->published()->with(['category', 'author'])->firstOrFail();
        $article->increment('view_count');

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->limit(3)
            ->get();

        return view('public.dakwah.show', compact('article', 'related'));
    }
}
