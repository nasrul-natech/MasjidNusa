@extends('layouts.public')

@section('title', 'Sejarah Kepengurusan')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6">
    <a href="{{ route('info-masjid.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Info Masjid
    </a>

    <h1 class="text-xl font-bold text-gray-900 mb-5">Sejarah Kepengurusan</h1>

    @forelse($managements as $mgmt)
    <div class="card mb-4">
        <div class="card-header">
            <div>
                <h3 class="font-bold text-gray-900">{{ $mgmt->title }}</h3>
                <p class="text-sm text-gray-500">{{ $mgmt->period }}</p>
            </div>
            @if($mgmt->is_current)
            <x-badge type="success">Aktif</x-badge>
            @endif
        </div>
        @if($mgmt->description)
        <div class="card-body pt-0">
            <p class="text-sm text-gray-600">{{ $mgmt->description }}</p>
        </div>
        @endif
    </div>
    @empty
    <div class="text-center py-12">
        <p class="text-gray-500">Belum ada data kepengurusan</p>
    </div>
    @endforelse
</div>
@endsection
