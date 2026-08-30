@extends('layouts.admin.keuangan')
@php($campaign = $campaign ?? new \App\Models\DonationCampaign)

@section('title', $campaign->exists ? 'Edit Program Donasi' : 'Buat Program Donasi')
@section('page-title', $campaign->exists ? 'Edit Program Donasi' : 'Buat Program Donasi')

@section('content')
<div class="max-w-2xl">
    <form action="{{ $campaign->exists ? route('admin.keuangan.campaigns.update', $campaign->id) : route('admin.keuangan.campaigns.store') }}"
          method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @if($campaign->exists) @method('PUT') @endif

        {{-- Section 1: Informasi Dasar --}}
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800 border-b border-gray-100 pb-3">Informasi Program</h3>

            <div>
                <label class="form-label">Nama Program <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $campaign->title ?? '') }}"
                    class="form-input" placeholder="Contoh: Renovasi Lantai Masjid 2025" required>
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Kategori</label>
                <select name="category_id" class="form-input">
                    <option value="">-- Tidak Ada Kategori --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $campaign->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    <a href="{{ route('admin.keuangan.donation-categories.index') }}" class="text-emerald-600 hover:underline">Kelola kategori</a>
                </p>
            </div>

            <div>
                <label class="form-label">Deskripsi Singkat</label>
                <textarea name="description" rows="3" class="form-input"
                    placeholder="Penjelasan singkat tentang program ini...">{{ old('description', $campaign->description ?? '') }}</textarea>
                @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Konten Lengkap</label>
                <textarea name="content" rows="6" class="form-input"
                    placeholder="Detail program, tujuan, penggunaan dana...">{{ old('content', $campaign->content ?? '') }}</textarea>
                @error('content') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Section 2: Target & Waktu --}}
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800 border-b border-gray-100 pb-3">Target & Periode</h3>

            <div>
                <label class="form-label">Target Dana <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rp</span>
                    <input type="number" name="target_amount" value="{{ old('target_amount', $campaign->target_amount ?? '') }}"
                        class="form-input pl-10" placeholder="10000000" min="10000" required>
                </div>
                @error('target_amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            @if($campaign->exists)
            <div class="space-y-3">
                <div>
                    <label class="form-label">Jumlah Terkumpul (Tersimpan)</label>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rp</span>
                            <input type="number" value="{{ $campaign->collected_amount }}"
                                class="form-input pl-10 bg-gray-50 font-semibold text-gray-800" readonly>
                        </div>
                        <span class="text-sm font-semibold {{ $campaign->progress_percent >= 100 ? 'text-emerald-600' : 'text-gray-500' }} shrink-0">
                            {{ $campaign->progress_percent }}%
                        </span>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 overflow-hidden">
                    <div class="px-4 py-2 bg-gray-100 border-b border-gray-200 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Asal Nilai Terkumpul</span>
                        <a href="{{ route('admin.keuangan.campaigns.show', $campaign->id) }}" class="text-xs text-emerald-600 hover:underline">Lihat semua &rarr;</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="text-xs text-gray-700">Donasi terverifikasi / lunas</span>
                                <span class="text-xs bg-emerald-100 text-emerald-700 rounded-full px-1.5 py-0.5 font-semibold">{{ $verifiedCount }} donasi</span>
                            </div>
                            <span class="text-xs font-semibold text-emerald-700">Rp {{ number_format($verifiedSum, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                                <span class="text-xs text-gray-700">Menunggu verifikasi</span>
                                <span class="text-xs bg-amber-100 text-amber-700 rounded-full px-1.5 py-0.5 font-semibold">{{ $pendingCount }} donasi</span>
                            </div>
                            <span class="text-xs font-semibold text-amber-600">Rp {{ number_format($pendingSum, 0, ',', '.') }}</span>
                        </div>
                        <div class="px-4 py-2.5 bg-blue-50">
                            <p class="text-xs text-blue-700">
                                Nilai terkumpul bertambah otomatis setiap admin memverifikasi pembayaran. Dana menunggu verifikasi belum masuk ke total ini.
                            </p>
                        </div>
                    </div>
                </div>

                @if($isDivergent && $verifiedCount > 0)
                <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 flex items-start gap-3">
                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-2.194-.833-2.964 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <div>
                        <p class="text-xs font-semibold text-amber-800">Nilai Tidak Sinkron</p>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Tersimpan: Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}
                            vs total terverifikasi: Rp {{ number_format($verifiedSum, 0, ',', '.') }}.
                            Gunakan tombol Sinkronkan di halaman Detail.
                        </p>
                    </div>
                </div>
                @endif

                @if($recentDonations->isNotEmpty())
                <div>
                    <p class="text-xs text-gray-500 mb-2 font-medium">5 Donasi Terverifikasi Terakhir</p>
                    <div class="space-y-1">
                        @foreach($recentDonations as $d)
                        <div class="flex items-center justify-between rounded-lg bg-white border border-gray-100 px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($d->is_anonymous ? 'H' : ($d->donor_name ?? 'A'), 0, 1)) }}
                                </span>
                                <span class="text-xs text-gray-700">{{ $d->is_anonymous ? 'Hamba Allah' : $d->donor_name }}</span>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-semibold text-emerald-700">Rp {{ number_format($d->amount, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-400">{{ $d->updated_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="rounded-lg bg-white border border-gray-100 px-3 py-3 text-center">
                    <p class="text-xs text-gray-400">Belum ada donasi terverifikasi. Nilai bertambah otomatis setelah pembayaran diverifikasi.</p>
                </div>
                @endif
            </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date"
                        value="{{ old('start_date', $campaign->start_date?->format('Y-m-d') ?? '') }}"
                        class="form-input">
                </div>
                <div>
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date"
                        value="{{ old('end_date', $campaign->end_date?->format('Y-m-d') ?? '') }}"
                        class="form-input">
                </div>
            </div>
            <p class="text-xs text-gray-400">Kosongkan tanggal jika program tidak memiliki batas waktu</p>
        </div>

        {{-- Section 3: Gambar & Pengaturan --}}
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800 border-b border-gray-100 pb-3">Gambar & Pengaturan</h3>

            @if($campaign->exists && $campaign->banner)
            <div>
                <p class="text-xs text-gray-500 mb-2">Banner Saat Ini</p>
                <img src="{{ $campaign->banner_url }}" class="h-36 w-full object-cover rounded-xl" alt="{{ $campaign->title }}">
            </div>
            @endif

            <div>
                <label class="form-label">Banner / Foto Program {{ !$campaign->exists ? '' : '(kosongkan jika tidak ingin ganti)' }}</label>
                <input type="file" name="banner" accept="image/*" class="form-input">
                <p class="text-xs text-gray-400 mt-1">Format JPG/PNG, maksimal 4MB. Rasio landscape (16:9) direkomendasikan.</p>
                @error('banner') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Status Program <span class="text-red-500">*</span></label>
                <select name="status" class="form-input" required>
                    <option value="draft" {{ old('status', $campaign->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft (belum tampil di publik)</option>
                    <option value="active" {{ old('status', $campaign->status ?? '') === 'active' ? 'selected' : '' }}>Aktif (tampil & bisa menerima donasi)</option>
                    <option value="selesai" {{ old('status', $campaign->status ?? '') === 'selesai' ? 'selected' : '' }}>Selesai (tampil, tidak menerima donasi baru)</option>
                    <option value="arsip" {{ old('status', $campaign->status ?? '') === 'arsip' ? 'selected' : '' }}>Arsip (tersembunyi dari publik)</option>
                </select>
                @error('status') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1" class="rounded text-emerald-600"
                    {{ old('is_featured', $campaign->is_featured ?? false) ? 'checked' : '' }}>
                <div>
                    <span class="text-sm text-gray-700 font-medium">Tampilkan di Halaman Beranda</span>
                    <p class="text-xs text-gray-400">Program akan muncul di bagian "Program Donasi Aktif" di halaman utama (maks. 3 program)</p>
                </div>
            </label>
        </div>

        {{-- Submit --}}
        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-8">
                {{ $campaign->exists ? 'Simpan Perubahan' : 'Buat Program' }}
            </button>
            <a href="{{ route('admin.keuangan.campaigns.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
