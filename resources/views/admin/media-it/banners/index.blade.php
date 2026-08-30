@extends('layouts.admin.media-it')

@section('title', 'Kelola Banner Hero')
@section('page-title', 'Kelola Banner Hero')

@section('content')
<div class="flex items-center justify-between mb-5">
    <p class="text-xs text-gray-500">Banner ditampilkan sebagai hero slider di halaman utama</p>
    <a href="{{ route('admin.media-it.banners.create') }}" class="btn-primary text-sm">+ Tambah Banner</a>
</div>

<div class="space-y-3">
    @forelse($banners as $banner)
    <div class="card overflow-hidden {{ !$banner->is_active ? 'opacity-60' : '' }}">
        <div class="relative" style="aspect-ratio: 16/7; max-height: 160px;">
            <img src="{{ asset('storage/' . $banner->image) }}" class="w-full h-full object-cover" alt="{{ $banner->title }}">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
            <div class="absolute bottom-3 left-3 right-3">
                <h3 class="text-white font-bold text-sm drop-shadow truncate">{{ $banner->title }}</h3>
            </div>
            <div class="absolute top-2 right-2 flex gap-1.5">
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ $banner->is_active ? 'bg-emerald-500 text-white' : 'bg-gray-500 text-white' }}">
                    {{ $banner->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
                @php
                    $typeColors = ['all' => 'bg-emerald-100 text-emerald-700', 'desktop' => 'bg-blue-100 text-blue-700', 'mobile' => 'bg-purple-100 text-purple-700'];
                    $typeLabels = ['all' => 'Semua', 'desktop' => 'Desktop', 'mobile' => 'Mobile'];
                @endphp
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ $typeColors[$banner->display_type ?? 'all'] }}">
                    {{ $typeLabels[$banner->display_type ?? 'all'] }}
                </span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-black/40 text-white">
                    #{{ $banner->sort_order }}
                </span>
            </div>
        </div>
        <div class="p-3 flex items-center justify-between">
            <div class="flex-1 min-w-0">
                @if($banner->link)
                <p class="text-xs text-blue-600 truncate">{{ $banner->link }}</p>
                @endif
                @if($banner->start_date || $banner->end_date)
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $banner->start_date?->format('d/m/Y') ?? '...' }} - {{ $banner->end_date?->format('d/m/Y') ?? '...' }}
                </p>
                @else
                <p class="text-xs text-gray-400 mt-0.5">Tanpa batas waktu</p>
                @endif
            </div>
            <div class="action-group shrink-0">
                <a href="{{ route('admin.media-it.banners.edit', $banner->id) }}" class="btn-action-blue">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form action="{{ route('admin.media-it.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Hapus banner ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-action-red">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-10 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <p class="text-gray-500 font-medium">Belum ada banner hero</p>
        <p class="text-gray-400 text-xs mt-1">Tambahkan banner untuk menampilkan hero slider di halaman utama</p>
        <a href="{{ route('admin.media-it.banners.create') }}" class="btn-primary text-sm mt-4 inline-block">+ Tambah Banner</a>
    </div>
    @endforelse
</div>

@if($banners->hasPages())
<div class="mt-5">{{ $banners->links() }}</div>
@endif

<div class="card p-4 mt-5 bg-blue-50 border-blue-200">
    <p class="text-sm font-medium text-blue-800">Tips Banner Hero</p>
    <ul class="text-xs text-blue-700 mt-1 space-y-0.5 list-disc list-inside">
        <li><strong>Desktop:</strong> Ukuran ideal 1920 x 540 px (landscape lebar, rasio ~3.5:1)</li>
        <li><strong>Mobile:</strong> Ukuran ideal 1080 x 810 px (portrait/square, rasio 4:3)</li>
        <li><strong>Semua:</strong> Ukuran 1920 x 640 px (otomatis di-crop sesuai perangkat)</li>
        <li>Sebaiknya buat versi terpisah untuk desktop dan mobile agar tampilan optimal</li>
        <li>Slider berganti otomatis setiap 6 detik, bisa digeser manual</li>
    </ul>
</div>
@endsection
