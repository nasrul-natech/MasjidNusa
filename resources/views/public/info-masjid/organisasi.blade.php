@extends('layouts.public')

@section('title', 'Struktur Organisasi')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6">
    <a href="{{ route('info-masjid.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 mb-5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Info Masjid
    </a>

    <h1 class="text-xl font-bold text-gray-900 mb-6">Struktur Organisasi</h1>

    @if($current)
    <div class="card mb-6">
        <div class="card-header">
            <div>
                <h2 class="font-bold text-gray-900">{{ $current->title }}</h2>
                <p class="text-sm text-gray-500">{{ $current->period }}</p>
            </div>
            <x-badge type="success">Aktif</x-badge>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach($current->organizationStructures as $structure)
                <div class="text-center">
                    <img src="{{ $structure->photo_url }}" alt="{{ $structure->name }}" class="w-16 h-16 rounded-full object-cover mx-auto mb-2 border-2 border-emerald-200">
                    <p class="text-sm font-bold text-gray-900">{{ $structure->name }}</p>
                    <p class="text-xs text-emerald-700 font-medium">{{ $structure->position }}</p>

                    {{-- Children --}}
                    @foreach($structure->children as $child)
                    <div class="mt-2 pt-2 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-700">{{ $child->name }}</p>
                        <p class="text-xs text-gray-500">{{ $child->position }}</p>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
