@extends('layouts.admin.media-it')
@php($article = $article ?? new \App\Models\Article)

@section('title', $article->exists ? 'Edit Artikel' : 'Tulis Artikel')
@section('page-title', $article->exists ? 'Edit Artikel' : 'Tulis Artikel Baru')

@section('content')
<form action="{{ $article->exists ? route('admin.media-it.articles.update', $article->id) : route('admin.media-it.articles.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($article->exists) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-5 space-y-4">
                <div>
                    <label class="form-label">Judul Artikel <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $article->title ?? '') }}" class="form-input text-lg font-semibold" required>
                </div>
                <div>
                    <label class="form-label">Ringkasan</label>
                    <textarea name="excerpt" class="form-input" rows="2" placeholder="Deskripsi singkat artikel untuk preview...">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                </div>
                <div>
                    <label class="form-label">Konten <span class="text-red-500">*</span></label>
                    <textarea name="content" class="form-input" rows="12" required>{{ old('content', $article->content ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card p-5 space-y-4">
                <h3 class="font-semibold text-gray-800 text-sm">Pengaturan Artikel</h3>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="draft" {{ old('status', $article->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="publish" {{ old('status', $article->status ?? '') === 'publish' ? 'selected' : '' }}>Publish</option>
                        <option value="arsip" {{ old('status', $article->status ?? '') === 'arsip' ? 'selected' : '' }}>Arsip</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-input">
                        <option value="">-- Tanpa Kategori --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $article->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="card p-5 space-y-3">
                <h3 class="font-semibold text-gray-800 text-sm">Thumbnail</h3>
                @if($article->exists && $article->thumbnail)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" class="w-full h-24 object-cover rounded-lg">
                @endif
                <input type="file" name="thumbnail" accept="image/*" class="form-input">
            </div>

            <div class="flex flex-col gap-2">
                <button type="submit" class="btn-primary w-full">Simpan</button>
                <a href="{{ route('admin.media-it.articles.index') }}" class="btn-secondary w-full text-center">Batal</a>
            </div>
        </div>
    </div>
</form>
@endsection
