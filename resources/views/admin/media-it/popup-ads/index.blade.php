@extends('layouts.admin.media-it')

@section('title', 'Popup Iklan')
@section('page-title', 'Kelola Popup Iklan')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-gray-900">{{ $counts['total'] }}</p>
        <p class="text-xs text-gray-500">Total Popup</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-emerald-600">{{ $counts['active'] }}</p>
        <p class="text-xs text-gray-500">Aktif</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $counts['image'] }}</p>
        <p class="text-xs text-gray-500">Tipe Gambar</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-purple-600">{{ $counts['link'] }}</p>
        <p class="text-xs text-gray-500">Tipe Link</p>
    </div>
</div>

{{-- Actions & Filter --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex gap-2">
        <a href="{{ route('admin.media-it.popup-ads.index') }}" class="px-3 py-1.5 text-xs rounded-lg {{ !request('type') && !request('status') ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Semua</a>
        <a href="{{ route('admin.media-it.popup-ads.index', ['type' => 'image']) }}" class="px-3 py-1.5 text-xs rounded-lg {{ request('type') === 'image' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Gambar</a>
        <a href="{{ route('admin.media-it.popup-ads.index', ['type' => 'link']) }}" class="px-3 py-1.5 text-xs rounded-lg {{ request('type') === 'link' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Link</a>
        <a href="{{ route('admin.media-it.popup-ads.index', ['status' => 'active']) }}" class="px-3 py-1.5 text-xs rounded-lg {{ request('status') === 'active' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Aktif</a>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.media-it.popup-ads.create', ['type' => 'image']) }}" class="btn-primary text-sm">+ Popup Gambar</a>
        <a href="{{ route('admin.media-it.popup-ads.create', ['type' => 'link']) }}" class="btn-secondary text-sm">+ Popup Link</a>
    </div>
</div>

{{-- Popup List --}}
<div class="space-y-3">
    @forelse($popups as $popup)
    <div class="card p-4 {{ !$popup->isCurrentlyActive() ? 'opacity-60' : '' }}">
        <div class="flex gap-4 items-start">
            <div class="relative shrink-0">
                <img src="{{ asset('storage/' . $popup->image) }}" class="w-20 h-20 object-cover rounded-xl" alt="{{ $popup->title }}">
                <span class="absolute -top-1 -right-1 px-1.5 py-0.5 text-[10px] font-bold rounded-md {{ $popup->ad_type === 'image' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                    {{ $popup->ad_type === 'image' ? 'IMG' : 'LINK' }}
                </span>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold text-gray-900 truncate">{{ $popup->title }}</h3>
                        @if($popup->ad_type === 'link' && $popup->link)
                        <p class="text-xs text-blue-600 truncate mt-0.5">{{ $popup->link }}</p>
                        @endif
                        @if($popup->content)
                        <p class="text-xs text-gray-500 truncate mt-0.5">{{ Str::limit($popup->content, 80) }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <x-badge type="{{ $popup->is_active ? 'success' : 'gray' }}">{{ $popup->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                    <span class="text-[10px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded">{{ $popup->duration }}s durasi</span>
                    <span class="text-[10px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded">Prioritas: {{ $popup->priority }}</span>

                    @if($popup->activity)
                    <span class="text-[10px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">{{ $popup->activity->title }}</span>
                    @endif

                    @if($popup->start_date || $popup->end_date)
                    <span class="text-[10px] text-gray-400">
                        {{ $popup->start_date?->format('d/m/Y') ?? '...' }} - {{ $popup->end_date?->format('d/m/Y') ?? '...' }}
                    </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                <form action="{{ route('admin.media-it.popup-ads.toggle', $popup->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg {{ $popup->is_active ? 'text-emerald-600 hover:bg-emerald-50' : 'text-gray-400 hover:bg-gray-50' }}" title="{{ $popup->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $popup->is_active ? 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' : 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21' }}"/></svg>
                    </button>
                </form>
                <a href="{{ route('admin.media-it.popup-ads.edit', $popup->id) }}" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                <form action="{{ route('admin.media-it.popup-ads.destroy', $popup->id) }}" method="POST" onsubmit="return confirm('Hapus popup ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <p class="text-gray-500 font-medium">Belum ada popup iklan</p>
        <p class="text-gray-400 text-sm mt-1">Buat popup gambar atau link untuk menampilkan iklan ke pengunjung</p>
    </div>
    @endforelse
</div>

<div class="mt-5">
    {{ $popups->links() }}
</div>

{{-- Info Box --}}
<div class="card p-4 mt-5 bg-amber-50 border-amber-200">
    <p class="text-sm font-medium text-amber-800">Tentang Popup Iklan</p>
    <ul class="text-xs text-amber-700 mt-1 space-y-0.5 list-disc list-inside">
        <li><strong>Popup Gambar:</strong> Hanya menampilkan gambar, cocok untuk poster/flyer. Klik di luar popup untuk menutup.</li>
        <li><strong>Popup Link:</strong> Gambar + judul + deskripsi + link. Klik akan membuka link tujuan.</li>
        <li><strong>Durasi:</strong> Waktu (detik) popup ditampilkan sebelum bisa ditutup otomatis.</li>
        <li><strong>Prioritas:</strong> Angka lebih kecil = tampil lebih dulu. Popup bisa tumpang tindih (ditampilkan bergantian).</li>
    </ul>
</div>
@endsection
