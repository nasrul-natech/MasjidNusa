<?php

namespace App\Http\Controllers\Admin\MediaIT;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['category', 'author'])->orderByDesc('created_at');
        if ($request->status) $query->where('status', $request->status);
        $articles = $query->paginate(15);
        $categories = ArticleCategory::all();
        return view('admin.media-it.articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $categories = ArticleCategory::all();
        $article = null;
        return view('admin.media-it.articles.form', compact('article', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:article_categories,id'],
            'status' => ['required', 'in:draft,publish,arsip'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . time();
        $data['author_id'] = auth()->id();

        if ($data['status'] === 'publish' && !auth()->user()->can('publish_content')) {
            return back()->with('error', 'Anda tidak memiliki izin menerbitkan konten.')->withInput();
        }

        if ($data['status'] === 'publish') {
            $data['published_at'] = now();
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('articles', 'public');
        }

        Article::create($data);
        return redirect()->route('admin.media-it.articles.index')->with('success', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article)
    {
        $categories = ArticleCategory::all();
        return view('admin.media-it.articles.form', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:article_categories,id'],
            'status' => ['required', 'in:draft,publish,arsip'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($data['status'] === 'publish' && $article->status !== 'publish' && !auth()->user()->can('publish_content')) {
            return back()->with('error', 'Anda tidak memiliki izin menerbitkan konten.')->withInput();
        }

        if ($data['status'] === 'publish' && !$article->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('articles', 'public');
        }

        $article->update($data);
        return redirect()->route('admin.media-it.articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return back()->with('success', 'Artikel dihapus.');
    }

    public function publish(int $id)
    {
        if (!auth()->user()->can('publish_content')) {
            return back()->with('error', 'Anda tidak memiliki izin menerbitkan konten.');
        }

        $article = Article::findOrFail($id);
        $article->update(['status' => 'publish', 'published_at' => now()]);
        return back()->with('success', 'Artikel dipublikasikan.');
    }
}
