@extends('layouts.public')

@section('title', $article->title)

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-5 max-w-2xl">
    <a href="{{ route('dakwah.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Dakwah & Artikel
    </a>

    @if($article->thumbnail)
    <img src="{{ $article->thumbnail_url }}" alt="{{ $article->title }}" class="w-full h-48 sm:h-64 object-cover rounded-2xl mb-5">
    @endif

    @if($article->category)
    <x-badge type="warning" class="mb-3">{{ $article->category->name }}</x-badge>
    @endif

    <h1 class="text-2xl font-bold text-gray-900 mb-3">{{ $article->title }}</h1>

    <div class="flex items-center gap-3 text-sm text-gray-500 mb-5 pb-5 border-b border-gray-100">
        @if($article->author)
        <img src="{{ $article->author->avatar_url }}" class="w-7 h-7 rounded-full object-cover">
        <span>{{ $article->author->name }}</span>
        <span>·</span>
        @endif
        <span>{{ $article->published_at->locale('id')->isoFormat('D MMMM Y') }}</span>
        <span>·</span>
        <span>{{ $article->view_count }} kali dibaca</span>
    </div>

    <div class="prose prose-sm max-w-none text-gray-800 leading-relaxed">
        {!! $article->content !!}
    </div>

    @if($related->count())
    <div class="mt-8 pt-6 border-t border-gray-100">
        <h2 class="font-bold text-gray-900 mb-4">Artikel Terkait</h2>
        <div class="space-y-3">
            @foreach($related as $rel)
            <a href="{{ route('dakwah.show', $rel->slug) }}" class="flex gap-3 items-center p-3 bg-white rounded-2xl border border-gray-100 hover:shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex-shrink-0 overflow-hidden">
                    @if($rel->thumbnail)
                    <img src="{{ $rel->thumbnail_url }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center">📖</div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 line-clamp-2">{{ $rel->title }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
