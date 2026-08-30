@extends('layouts.public')

@section('title', 'Info Masjid')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6">
    <h1 class="text-xl font-bold text-gray-900 mb-5">Informasi Masjid</h1>

    {{-- Navigation Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <a href="{{ route('info-masjid.index') }}" class="card p-4 text-center hover:shadow-md transition-shadow border-emerald-500 border-2">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <p class="text-xs font-semibold text-emerald-700">Profil Masjid</p>
        </a>
        <a href="{{ route('info-masjid.history') }}" class="card p-4 text-center hover:shadow-md transition-shadow">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-semibold text-gray-700">Sejarah</p>
        </a>
        <a href="{{ route('info-masjid.management') }}" class="card p-4 text-center hover:shadow-md transition-shadow">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-xs font-semibold text-gray-700">Kepengurusan</p>
        </a>
        <a href="{{ route('info-masjid.organization') }}" class="card p-4 text-center hover:shadow-md transition-shadow">
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </div>
            <p class="text-xs font-semibold text-gray-700">Organisasi</p>
        </a>
        <a href="{{ route('info-sistem') }}" class="card p-4 text-center hover:shadow-md transition-shadow">
            <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <p class="text-xs font-semibold text-gray-700">Info Sistem</p>
        </a>
    </div>

    @if($mosque)
    {{-- Mosque photo --}}
    @if($mosque->banner)
    <div class="rounded-2xl overflow-hidden mb-5">
        <img src="{{ $mosque->banner_url }}" alt="{{ $mosque->name }}" class="w-full h-48 object-cover">
    </div>
    @endif

    <div class="card p-5 mb-5">
        <h2 class="text-lg font-bold text-gray-900 mb-3">{{ $mosque->name }}</h2>
        @if($mosque->description)
        <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $mosque->description }}</p>
        @endif

        <div class="space-y-2 text-sm">
            @if($mosque->address)
            <div class="flex gap-2">
                <span class="text-gray-400 flex-shrink-0">📍</span>
                <span class="text-gray-700">{{ $mosque->address }}</span>
            </div>
            @endif
            @if($mosque->phone)
            <div class="flex gap-2">
                <span class="text-gray-400 flex-shrink-0">📞</span>
                <a href="tel:{{ $mosque->phone }}" class="text-emerald-600">{{ $mosque->phone }}</a>
            </div>
            @endif
            @if($mosque->email)
            <div class="flex gap-2">
                <span class="text-gray-400 flex-shrink-0">📧</span>
                <a href="mailto:{{ $mosque->email }}" class="text-emerald-600">{{ $mosque->email }}</a>
            </div>
            @endif
        </div>
    </div>

    @if($mosque->vision || $mosque->mission)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        @if($mosque->vision)
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">🎯</div>
                <h3 class="font-bold text-gray-900">Visi</h3>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $mosque->vision }}</p>
        </div>
        @endif
        @if($mosque->mission)
        <div class="card p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">🚀</div>
                <h3 class="font-bold text-gray-900">Misi</h3>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $mosque->mission }}</p>
        </div>
        @endif
    </div>
    @endif
    @endif
</div>
@endsection
