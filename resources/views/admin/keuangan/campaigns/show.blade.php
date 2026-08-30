@extends('layouts.admin.keuangan')

@section('title', $campaign->title)
@section('page-title', 'Detail Program Donasi')

@section('content')
<div class="max-w-4xl">

    {{-- Header Card --}}
    <div class="card overflow-hidden mb-5">
        @if($campaign->banner)
        <div class="h-48 overflow-hidden">
            <img src="{{ $campaign->banner_url }}" alt="{{ $campaign->title }}" class="w-full h-full object-cover">
        </div>
        @endif
        <div class="p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                        @php
                            $statusColors = ['draft' => 'yellow', 'active' => 'success', 'selesai' => 'info', 'arsip' => 'gray'];
                            $statusLabels = ['draft' => 'Draft', 'active' => 'Aktif', 'selesai' => 'Selesai', 'arsip' => 'Arsip'];
                        @endphp
                        <x-badge type="{{ $statusColors[$campaign->status] ?? 'gray' }}">{{ $statusLabels[$campaign->status] ?? $campaign->status }}</x-badge>
                        @if($campaign->is_featured) <x-badge type="warning">⭐ Tampil di Beranda</x-badge> @endif
                        @if($campaign->category) <x-badge type="info">{{ $campaign->category->name }}</x-badge> @endif
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $campaign->title }}</h2>
                    @if($campaign->description)
                    <p class="text-gray-600 text-sm mt-1">{{ $campaign->description }}</p>
                    @endif
                </div>
                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('admin.keuangan.campaigns.edit', $campaign->id) }}" class="btn-primary text-sm">Edit</a>
                    <a href="{{ route('donasi.show', $campaign->slug) }}" target="_blank" class="btn-secondary text-sm">Lihat Publik</a>
                </div>
            </div>

            {{-- Progress --}}
            <div class="mt-5 p-4 bg-emerald-50 rounded-xl">
                <div class="flex justify-between items-end mb-2">
                    <div>
                        <p class="text-xs text-gray-500">Terkumpul</p>
                        <p class="text-xl font-bold text-emerald-700">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Target</p>
                        <p class="font-semibold text-gray-700">Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="h-3 bg-white rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $campaign->progress_percent }}%"></div>
                </div>
                <p class="text-right text-xs font-semibold text-emerald-600 mt-1">{{ $campaign->progress_percent }}% tercapai</p>
            </div>

            @if($campaign->start_date || $campaign->end_date)
            <div class="flex gap-4 mt-3 text-sm text-gray-600">
                @if($campaign->start_date) <span>Mulai: <strong>{{ $campaign->start_date->format('d M Y') }}</strong></span> @endif
                @if($campaign->end_date) <span>Selesai: <strong>{{ $campaign->end_date->format('d M Y') }}</strong></span> @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Donation Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $donationStats['total_count'] }}</p>
            <p class="text-xs text-gray-500">Total Donatur</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $donationStats['verified_count'] }}</p>
            <p class="text-xs text-gray-500">Terverifikasi</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-amber-500">{{ $donationStats['pending_count'] }}</p>
            <p class="text-xs text-gray-500">Menunggu</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-base font-bold text-blue-600">Rp {{ number_format($donationStats['total_amount'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500">Total Dana Masuk</p>
        </div>
    </div>

    {{-- Adjust Amount --}}
    <div class="card p-5 mb-5" x-data="{ showAdjust: false }">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div>
                <h3 class="font-semibold text-gray-800">Sesuaikan Jumlah Terkumpul</h3>
                <p class="text-xs text-gray-500 mt-0.5">Gunakan jika ada donasi offline atau koreksi data</p>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('admin.keuangan.campaigns.sync-amount', $campaign->id) }}" method="POST"
                      onsubmit="return confirm('Sinkronkan nilai terkumpul dari total donasi terverifikasi di database?')">
                    @csrf
                    <button type="submit" class="btn-action-green">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Sinkronkan dari DB
                    </button>
                </form>
                <button @click="showAdjust = !showAdjust" class="btn-secondary text-sm">Ubah Manual</button>
            </div>
        </div>
        <div x-show="showAdjust" x-transition class="mt-4 border-t border-gray-100 pt-4">
            <form action="{{ route('admin.keuangan.campaigns.adjust-amount', $campaign->id) }}" method="POST" class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="form-label text-xs">Jumlah Terkumpul Baru</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-semibold">Rp</span>
                        <input type="number" name="collected_amount" value="{{ $campaign->collected_amount }}" class="form-input pl-10" min="0" required>
                    </div>
                </div>
                <div class="flex-1">
                    <label class="form-label text-xs">Alasan / Catatan</label>
                    <input type="text" name="reason" class="form-input" placeholder="Contoh: Koreksi donasi offline" required>
                </div>
                <button type="submit" class="btn-primary text-sm h-[38px]">Simpan</button>
            </form>
        </div>
    </div>

    {{-- Donation History --}}
    <div class="card overflow-hidden">
        <div class="card-header flex justify-between items-center">
            <h3 class="font-semibold text-gray-800">Riwayat Donasi Masuk</h3>
            <a href="{{ route('admin.keuangan.donations.index') }}?campaign={{ $campaign->id }}" class="text-xs text-emerald-600 hover:underline">Lihat semua</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Donatur</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Pesan</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($donations as $donation)
                @php
                    $statusColors = ['pending'=>'yellow','waiting_verification'=>'warning','verified'=>'success','paid'=>'success','rejected'=>'danger'];
                    $statusLabels = ['pending'=>'Menunggu','waiting_verification'=>'Perlu Verifikasi','verified'=>'Terverifikasi','paid'=>'Lunas','rejected'=>'Ditolak'];
                @endphp
                <tr>
                    <td>
                        <p class="font-medium text-sm">{{ $donation->is_anonymous ? 'Hamba Allah' : $donation->donor_name }}</p>
                        <p class="text-xs text-gray-400">{{ $donation->donor_email }}</p>
                    </td>
                    <td class="font-semibold text-emerald-700">Rp {{ number_format($donation->amount, 0, ',', '.') }}</td>
                    <td><x-badge type="{{ $statusColors[$donation->status] ?? 'gray' }}">{{ $statusLabels[$donation->status] ?? $donation->status }}</x-badge></td>
                    <td class="text-xs text-gray-500 max-w-[150px] truncate">{{ $donation->message ?? '-' }}</td>
                    <td class="text-xs text-gray-500">{{ $donation->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-400">Belum ada donasi masuk</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $donations->links() }}
        </div>
    </div>

    <div class="mt-5">
        <a href="{{ route('admin.keuangan.campaigns.index') }}" class="btn-secondary text-sm">&larr; Kembali ke Daftar Program</a>
    </div>
</div>
@endsection
