@extends('layouts.public')

@section('title', $activity->title)

@section('content')
<div class="max-w-screen-xl mx-auto">
    {{-- Banner --}}
    @if($activity->banner)
    <div class="relative">
        <img src="{{ $activity->banner_url }}" alt="{{ $activity->title }}" class="w-full h-52 sm:h-72 object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
        <a href="{{ route('kegiatan.index') }}" class="absolute top-4 left-4 w-9 h-9 bg-black/30 rounded-xl flex items-center justify-center text-white backdrop-blur-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="absolute bottom-4 left-4 right-4">
            <h1 class="text-xl font-bold text-white leading-tight">{{ $activity->title }}</h1>
        </div>
    </div>
    @else
    <div class="px-4 pt-4">
        <a href="{{ route('kegiatan.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kegiatan
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $activity->title }}</h1>
    </div>
    @endif

    <div class="px-4 py-5 max-w-2xl">
        {{-- Info --}}
        <div class="card p-4 mb-5 space-y-2.5">
            <div class="flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-gray-700">{{ $activity->start_date->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    @if($activity->end_date && $activity->end_date->format('d') !== $activity->start_date->format('d'))
                    s/d {{ $activity->end_date->locale('id')->isoFormat('D MMMM Y') }}
                    @endif
                </span>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-gray-700">{{ $activity->start_date->format('H:i') }} WIB</span>
            </div>
            @if($activity->location)
            <div class="flex items-center gap-3 text-sm">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                <span class="text-gray-700">{{ $activity->location }}</span>
            </div>
            @endif
            @if($activity->category)
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                <x-badge type="info">{{ $activity->category->name }}</x-badge>
            </div>
            @endif
        </div>

        {{-- Description --}}
        @if($activity->content)
        <div class="prose prose-sm max-w-none text-gray-700 mb-6">
            {!! $activity->content !!}
        </div>
        @elseif($activity->description)
        <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ $activity->description }}</p>
        @endif

        {{-- Gallery --}}
        @if($activity->galleries->count())
        <h2 class="font-bold text-gray-900 mb-3">Galeri</h2>
        <div class="grid grid-cols-3 gap-2 mb-6">
            @foreach($activity->galleries as $gallery)
            <a href="{{ $gallery->image_url }}" target="_blank">
                <img src="{{ $gallery->image_url }}" alt="{{ $gallery->caption }}" class="w-full h-24 object-cover rounded-xl">
            </a>
            @endforeach
        </div>
        @endif

        {{-- Related --}}
        @if($related->count())
        <h2 class="font-bold text-gray-900 mb-3">Kegiatan Lainnya</h2>
        <div class="space-y-3">
            @foreach($related as $rel)
            <a href="{{ route('kegiatan.show', $rel->slug) }}" class="flex gap-3 items-center p-3 bg-white rounded-2xl border border-gray-100 hover:shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex-shrink-0 overflow-hidden">
                    @if($rel->banner)
                    <img src="{{ $rel->banner_url }}" class="w-full h-full object-cover">
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 line-clamp-1">{{ $rel->title }}</p>
                    <p class="text-xs text-gray-500">{{ $rel->start_date->locale('id')->isoFormat('D MMM Y') }}</p>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
