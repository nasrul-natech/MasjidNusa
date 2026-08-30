@extends('layouts.public')

@section('title', 'Sejarah Masjid')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6 max-w-2xl">
    <a href="{{ route('info-masjid.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 mb-5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Info Masjid
    </a>

    <h1 class="text-xl font-bold text-gray-900 mb-6">Sejarah Masjid</h1>

    @if($mosque && $mosque->histories->count())
    <div class="relative">
        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-emerald-200"></div>
        <div class="space-y-6">
            @foreach($mosque->histories as $history)
            <div class="relative pl-10">
                <div class="absolute left-2 top-1.5 w-5 h-5 rounded-full bg-emerald-700 border-4 border-white shadow-sm"></div>
                <div class="card p-4">
                    @if($history->event_date)
                    <p class="text-xs text-emerald-600 font-semibold mb-1">{{ $history->event_date->locale('id')->isoFormat('MMMM Y') }}</p>
                    @endif
                    <h3 class="font-bold text-gray-900 mb-2">{{ $history->title }}</h3>
                    <div class="text-sm text-gray-600 leading-relaxed">
                        {!! nl2br(e($history->content)) !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-12">
        <p class="text-gray-500">Belum ada data sejarah</p>
    </div>
    @endif
</div>
@endsection
