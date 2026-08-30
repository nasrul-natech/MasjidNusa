@extends('layouts.public')

@section('title', 'Dakwah & Artikel')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6">
    <h1 class="text-xl font-bold text-gray-900 mb-5">Dakwah & Artikel</h1>

    {{-- Search --}}
    <form class="mb-4">
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="q" value="{{ $search }}" class="form-input pl-9 pr-4 py-2.5" placeholder="Cari artikel...">
        </div>
    </form>

    {{-- Categories --}}
    <div class="overflow-x-auto -mx-4 px-4 mb-5 scrollbar-hide">
        <div class="flex gap-2 pb-1" style="width: max-content">
            <a href="{{ route('dakwah.index') }}" class="px-4 py-1.5 rounded-full text-xs font-semibold {{ !$category ? 'bg-emerald-700 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">Semua</a>
            @foreach($categories as $cat)
            <a href="{{ route('dakwah.index', ['category' => $cat->slug]) }}" class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap {{ $category === $cat->slug ? 'bg-emerald-700 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">{{ $cat->name }}</a>
            @endforeach
        </div>
    </div>

    {{-- Articles --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($articles as $article)
        <a href="{{ route('dakwah.show', $article->slug) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
            <div class="h-40 bg-amber-50 overflow-hidden">
                @if($article->thumbnail)
                <img src="{{ $article->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $article->title }}">
                @else
                <div class="w-full h-full flex items-center justify-center text-4xl">📖</div>
                @endif
            </div>
            <div class="p-4">
                @if($article->category)
                <x-badge type="warning" class="mb-2 text-[10px]">{{ $article->category->name }}</x-badge>
                @endif
                <h3 class="font-bold text-gray-900 text-sm line-clamp-2 mb-2">{{ $article->title }}</h3>
                @if($article->excerpt)
                <p class="text-xs text-gray-500 line-clamp-2">{{ $article->excerpt }}</p>
                @endif
                <div class="flex items-center gap-2 mt-2 text-xs text-gray-400">
                    <span>{{ $article->published_at->locale('id')->isoFormat('D MMM Y') }}</span>
                    <span>·</span>
                    <span>{{ $article->view_count }} pembaca</span>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-16">
            <div class="text-5xl mb-3">📖</div>
            <p class="text-gray-500 font-medium">Belum ada artikel</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $articles->appends(request()->query())->links() }}</div>
</div>
@endsection
