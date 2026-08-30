@extends('layouts.super-admin')

@section('title', 'Manajemen Modul')
@section('page-title', 'Manajemen Modul Sistem')

@section('content')
<div class="p-4 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-700 mb-5 flex items-start gap-2">
    @include('components.icons.puzzle', ['class' => 'w-5 h-5 shrink-0 mt-0.5'])
    <span>Modul inti tidak bisa dinonaktifkan. Nonaktifkan modul opsional untuk menyembunyikan fitur dari publik.</span>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    @foreach($modules as $module)
    <div class="card p-4 flex justify-between items-center gap-4">
        <div class="flex items-start gap-3 flex-1 min-w-0">
            <div class="w-10 h-10 {{ $module->is_active ? 'bg-emerald-100' : 'bg-gray-100' }} rounded-xl flex items-center justify-center shrink-0">
                @include('components.icons.puzzle', ['class' => 'w-5 h-5 ' . ($module->is_active ? 'text-emerald-700' : 'text-gray-400')])
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900">{{ $module->name }}</p>
                @if($module->description)
                <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $module->description }}</p>
                @endif
                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                    @if($module->is_core)
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Modul Inti</span>
                    @endif
                    <span class="font-mono text-xs text-gray-400">{{ $module->slug }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <x-badge type="{{ $module->is_active ? 'success' : 'gray' }}">{{ $module->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
            @if(!$module->is_core)
            <form action="{{ route('super-admin.modules.toggle', $module->id) }}" method="POST">
                @csrf
                <button type="submit" class="{{ $module->is_active ? 'btn-action-red' : 'btn-action-green' }}">
                    {{ $module->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
