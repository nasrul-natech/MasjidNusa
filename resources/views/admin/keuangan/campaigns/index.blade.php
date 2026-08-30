@extends('layouts.admin.keuangan')

@section('title', 'Program Donasi')
@section('page-title', 'Program Donasi')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        <p class="text-xs text-gray-500">Total Program</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-emerald-600">{{ $stats['active'] }}</p>
        <p class="text-xs text-gray-500">Aktif</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-amber-600">{{ $stats['featured'] }}</p>
        <p class="text-xs text-gray-500">Tampil di Beranda</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-lg font-bold text-blue-600">Rp {{ number_format($stats['total_collected'] / 1000000, 1, ',', '.') }}jt</p>
        <p class="text-xs text-gray-500">Total Terkumpul</p>
    </div>
</div>

{{-- Filter + Actions --}}
<div class="flex flex-wrap items-end gap-3 mb-5">
    <form method="GET" class="flex flex-wrap gap-2 flex-1">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari program..." class="form-input text-sm w-48">
        <select name="status" class="form-input text-sm w-36">
            <option value="">Semua Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            <option value="arsip" {{ request('status') === 'arsip' ? 'selected' : '' }}>Arsip</option>
        </select>
        <select name="category" class="form-input text-sm w-40">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary text-sm px-4">Filter</button>
    </form>
    <div class="flex gap-2">
        <a href="{{ route('admin.keuangan.donation-categories.index') }}" class="btn-secondary text-sm">Kelola Kategori</a>
        <a href="{{ route('admin.keuangan.campaigns.create') }}" class="btn-primary text-sm">+ Program Baru</a>
    </div>
</div>

{{-- Campaign Table --}}
<div class="card overflow-hidden">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Program Donasi</th>
                <th>Kategori</th>
                <th>Progress</th>
                <th>Donasi</th>
                <th>Status</th>
                <th>Beranda</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($campaigns as $campaign)
            @php
                $statusColors = ['draft' => 'yellow', 'active' => 'success', 'selesai' => 'info', 'arsip' => 'gray'];
                $statusLabels = ['draft' => 'Draft', 'active' => 'Aktif', 'selesai' => 'Selesai', 'arsip' => 'Arsip'];
            @endphp
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 overflow-hidden shrink-0">
                            @if($campaign->banner)
                            <img src="{{ $campaign->banner_url }}" class="w-full h-full object-cover" alt="{{ $campaign->title }}">
                            @else
                            <div class="w-full h-full flex items-center justify-center text-base">💝</div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-sm text-gray-900 truncate max-w-[180px]">{{ $campaign->title }}</p>
                            @if($campaign->end_date)
                            <p class="text-xs text-gray-400">Berakhir: {{ $campaign->end_date->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="text-sm text-gray-600">{{ $campaign->category?->name ?? '-' }}</td>
                <td>
                    <div class="min-w-[120px]">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Rp {{ number_format($campaign->collected_amount / 1000, 0, ',', '.') }}rb</span>
                            <span class="font-semibold text-emerald-600">{{ $campaign->progress_percent }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $campaign->progress_percent }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">Target: Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</p>
                    </div>
                </td>
                <td class="text-center text-sm font-semibold text-gray-700">{{ $campaign->donations_count }}</td>
                <td><x-badge type="{{ $statusColors[$campaign->status] ?? 'gray' }}">{{ $statusLabels[$campaign->status] ?? $campaign->status }}</x-badge></td>
                <td class="text-center">
                    <form action="{{ route('admin.keuangan.campaigns.toggle-featured', $campaign->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-lg" title="{{ $campaign->is_featured ? 'Tampil di beranda' : 'Tidak di beranda' }}">
                            {{ $campaign->is_featured ? '⭐' : '☆' }}
                        </button>
                    </form>
                </td>
                <td>
                    <div class="action-group">
                        <a href="{{ route('admin.keuangan.campaigns.show', $campaign->id) }}" class="btn-action-green">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detail
                        </a>
                        <a href="{{ route('admin.keuangan.campaigns.edit', $campaign->id) }}" class="btn-action-blue">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                        @if($campaign->donations_count === 0)
                        <form action="{{ route('admin.keuangan.campaigns.destroy', $campaign->id) }}" method="POST" onsubmit="return confirm('Hapus program ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-action-red">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-12">
                    <div class="flex flex-col items-center gap-3">
                        <div class="text-4xl">💝</div>
                        <p class="text-gray-500 font-medium">Belum ada program donasi</p>
                        <a href="{{ route('admin.keuangan.campaigns.create') }}" class="btn-primary text-sm">+ Buat Program Pertama</a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $campaigns->links() }}
</div>

{{-- Info Box --}}
<div class="card p-4 mt-5 bg-blue-50 border-blue-200">
    <p class="text-sm font-medium text-blue-800">Tentang Program Donasi</p>
    <ul class="text-xs text-blue-700 mt-1 space-y-0.5 list-disc list-inside">
        <li>Program donasi adalah penggalangan dana untuk tujuan tertentu (renovasi, operasional, sosial, dll)</li>
        <li>Program yang <strong>Aktif</strong> akan tampil di halaman publik dan bisa menerima donasi</li>
        <li>Klik bintang (⭐) untuk menampilkan/menyembunyikan program di halaman beranda</li>
        <li>Program yang sudah memiliki donasi tidak bisa dihapus, arsipkan saja</li>
    </ul>
</div>
@endsection
