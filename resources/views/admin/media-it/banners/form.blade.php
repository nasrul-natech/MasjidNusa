@extends('layouts.admin.media-it')
@php($banner = $banner ?? new \App\Models\Banner)

@section('title', $banner->exists ? 'Edit Banner' : 'Tambah Banner')
@section('page-title', $banner->exists ? 'Edit Banner Hero' : 'Tambah Banner Hero')

@section('content')
<div class="max-w-xl" x-data="{
    displayType: '{{ old('display_type', $banner->display_type ?? 'all') }}',
    preview: null
}">
    <form action="{{ $banner->exists ? route('admin.media-it.banners.update', $banner->id) : route('admin.media-it.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if($banner->exists) @method('PUT') @endif

        {{-- Display Type Selection --}}
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800">Target Tampilan</h3>
            <p class="text-xs text-gray-500 -mt-2">Pilih di perangkat mana banner ini akan ditampilkan</p>

            <div class="grid grid-cols-3 gap-3">
                <label class="relative cursor-pointer">
                    <input type="radio" name="display_type" value="all" x-model="displayType" class="sr-only peer">
                    <div class="card p-3 text-center border-2 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 border-transparent hover:border-gray-200">
                        <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-xs font-semibold" :class="displayType === 'all' ? 'text-emerald-700' : 'text-gray-600'">Semua</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Desktop & Mobile</p>
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="display_type" value="desktop" x-model="displayType" class="sr-only peer">
                    <div class="card p-3 text-center border-2 transition peer-checked:border-blue-500 peer-checked:bg-blue-50 border-transparent hover:border-gray-200">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-xs font-semibold" :class="displayType === 'desktop' ? 'text-blue-700' : 'text-gray-600'">Desktop</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Layar lebar</p>
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="display_type" value="mobile" x-model="displayType" class="sr-only peer">
                    <div class="card p-3 text-center border-2 transition peer-checked:border-purple-500 peer-checked:bg-purple-50 border-transparent hover:border-gray-200">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-xs font-semibold" :class="displayType === 'mobile' ? 'text-purple-700' : 'text-gray-600'">Mobile</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Smartphone</p>
                    </div>
                </label>
            </div>
            @error('display_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

            {{-- Size Guide --}}
            <div class="rounded-xl p-3 text-xs"
                :class="{
                    'bg-emerald-50 border border-emerald-200': displayType === 'all',
                    'bg-blue-50 border border-blue-200': displayType === 'desktop',
                    'bg-purple-50 border border-purple-200': displayType === 'mobile'
                }">
                <template x-if="displayType === 'all'">
                    <div>
                        <p class="font-semibold text-emerald-800 mb-1">Panduan Ukuran - Semua Perangkat</p>
                        <ul class="text-emerald-700 space-y-0.5 list-disc list-inside">
                            <li>Ukuran: <strong>1920 x 640 px</strong> (rasio 3:1)</li>
                            <li>Teks penting harus di area tengah (karena akan di-crop pada mobile)</li>
                            <li>Gunakan gambar landscape beresolusi tinggi</li>
                        </ul>
                    </div>
                </template>
                <template x-if="displayType === 'desktop'">
                    <div>
                        <p class="font-semibold text-blue-800 mb-1">Panduan Ukuran - Desktop</p>
                        <ul class="text-blue-700 space-y-0.5 list-disc list-inside">
                            <li>Ukuran ideal: <strong>1920 x 540 px</strong> (rasio ~3.5:1)</li>
                            <li>Minimum: 1200 x 400 px</li>
                            <li>Hanya tampil di layar lebar (laptop/PC)</li>
                            <li>Cocok untuk banner panoramik / landscape lebar</li>
                        </ul>
                    </div>
                </template>
                <template x-if="displayType === 'mobile'">
                    <div>
                        <p class="font-semibold text-purple-800 mb-1">Panduan Ukuran - Mobile</p>
                        <ul class="text-purple-700 space-y-0.5 list-disc list-inside">
                            <li>Ukuran ideal: <strong>1080 x 810 px</strong> (rasio 4:3)</li>
                            <li>Minimum: 640 x 480 px</li>
                            <li>Hanya tampil di smartphone</li>
                            <li>Cocok untuk poster / gambar portrait & square</li>
                        </ul>
                    </div>
                </template>
            </div>
        </div>

        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800">Informasi Banner</h3>

            <div>
                <label class="form-label">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $banner->title ?? '') }}" class="form-input" required placeholder="Contoh: Selamat Datang di MasjidNusa">
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Link Tujuan</label>
                <input type="text" name="link" value="{{ old('link', $banner->link ?? '') }}" class="form-input" placeholder="https://... atau /donasi (opsional)">
                <p class="text-xs text-gray-400 mt-1">Klik pada banner akan mengarahkan ke URL ini</p>
                @error('link') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $banner->start_date?->format('Y-m-d') ?? '') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $banner->end_date?->format('Y-m-d') ?? '') }}" class="form-input">
                </div>
            </div>
            <p class="text-xs text-gray-400">Kosongkan untuk ditampilkan tanpa batas waktu</p>
            @error('end_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

            <div>
                <label class="form-label">Urutan Tampil</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" class="form-input" min="0">
                <p class="text-xs text-gray-400 mt-1">Angka lebih kecil tampil lebih dulu</p>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600" {{ old('is_active', $banner->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm">Aktifkan banner</span>
            </label>
        </div>

        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800">Gambar Banner</h3>

            @if($banner->exists && $banner->image)
            <div>
                <p class="text-xs text-gray-500 mb-2">Gambar Saat Ini</p>
                <div class="rounded-xl overflow-hidden border border-gray-200" style="max-height: 200px;">
                    <img src="{{ asset('storage/' . $banner->image) }}" class="w-full h-full object-cover">
                </div>
            </div>
            @endif

            <div>
                <label class="form-label">{{ $banner->exists ? 'Ganti Gambar' : 'Upload Gambar' }} {{ !$banner->exists ? '*' : '' }}</label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="form-input"
                    {{ !$banner->exists ? 'required' : '' }}
                    @change="preview = URL.createObjectURL($event.target.files[0])">
                <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, WebP. Maksimal 5MB</p>
                @error('image') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror

                <div x-show="preview" x-cloak class="mt-3">
                    <p class="text-xs text-gray-500 mb-1">Preview:</p>
                    <div class="rounded-xl overflow-hidden border border-gray-200" style="max-height: 200px;">
                        <img :src="preview" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6">Simpan</button>
            <a href="{{ route('admin.media-it.banners.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
