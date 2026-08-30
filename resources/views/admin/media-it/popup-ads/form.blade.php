@extends('layouts.admin.media-it')
@php($popupAd = $popupAd ?? new \App\Models\PopupAd)

@section('title', $popupAd->exists ? 'Edit Popup' : 'Buat Popup Iklan')
@section('page-title', $popupAd->exists ? 'Edit Popup Iklan' : 'Buat Popup Iklan')

@section('content')
<div class="max-w-xl">
    {{-- Type Selector (only for create) --}}
    @unless($popupAd->exists)
    <div class="flex gap-3 mb-5">
        <a href="{{ route('admin.media-it.popup-ads.create', ['type' => 'image']) }}"
           class="flex-1 card p-4 text-center border-2 transition {{ $adType === 'image' ? 'border-blue-500 bg-blue-50' : 'border-transparent hover:border-gray-200' }}">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <p class="font-semibold text-sm {{ $adType === 'image' ? 'text-blue-700' : 'text-gray-700' }}">Hanya Gambar</p>
            <p class="text-xs text-gray-500 mt-0.5">Popup berisi gambar saja (poster/flyer)</p>
        </a>
        <a href="{{ route('admin.media-it.popup-ads.create', ['type' => 'link']) }}"
           class="flex-1 card p-4 text-center border-2 transition {{ $adType === 'link' ? 'border-purple-500 bg-purple-50' : 'border-transparent hover:border-gray-200' }}">
            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </div>
            <p class="font-semibold text-sm {{ $adType === 'link' ? 'text-purple-700' : 'text-gray-700' }}">Gambar + Link</p>
            <p class="text-xs text-gray-500 mt-0.5">Popup dengan gambar, deskripsi, dan link tujuan</p>
        </a>
    </div>
    @endunless

    <form action="{{ $popupAd->exists ? route('admin.media-it.popup-ads.update', $popupAd->id) : route('admin.media-it.popup-ads.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if($popupAd->exists) @method('PUT') @endif
        <input type="hidden" name="ad_type" value="{{ old('ad_type', $popupAd->ad_type ?? $adType) }}">

        {{-- Basic Info --}}
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg {{ $adType === 'image' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }} flex items-center justify-center text-xs font-bold">1</span>
                Informasi Dasar
            </h3>

            <div>
                <label class="form-label">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $popupAd->title ?? '') }}" class="form-input" required placeholder="Contoh: Promo Donasi Ramadhan">
                @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            @if($popupAd->exists && $popupAd->image)
            <div>
                <p class="text-xs text-gray-500 mb-1">Gambar Saat Ini</p>
                <img src="{{ asset('storage/' . $popupAd->image) }}" class="h-32 object-cover rounded-xl">
            </div>
            @endif

            <div>
                <label class="form-label">Gambar {{ !$popupAd->exists ? '*' : '' }}</label>
                <input type="file" name="image" accept="image/*" class="form-input" {{ !$popupAd->exists ? 'required' : '' }}>
                <p class="text-xs text-gray-400 mt-1">Format: JPG/PNG, maksimal 4MB. Disarankan proporsi potrait atau square.</p>
                @error('image') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Link Info (only for link type) --}}
        @if($adType === 'link')
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-bold">2</span>
                Informasi Link
            </h3>

            <div>
                <label class="form-label">URL / Link Tujuan <span class="text-red-500">*</span></label>
                <input type="text" name="link" value="{{ old('link', $popupAd->link ?? '') }}" class="form-input" placeholder="https://... atau /donasi" required>
                @error('link') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Deskripsi Singkat</label>
                <textarea name="content" rows="3" class="form-input" placeholder="Deskripsi yang ditampilkan di popup...">{{ old('content', $popupAd->content ?? '') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Maksimal 500 karakter</p>
                @error('content') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Kegiatan Terkait</label>
                <select name="activity_id" class="form-input">
                    <option value="">-- Tidak Ada --</option>
                    @foreach($activities as $activity)
                    <option value="{{ $activity->id }}" {{ old('activity_id', $popupAd->activity_id ?? '') == $activity->id ? 'selected' : '' }}>{{ $activity->title }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Opsional: hubungkan popup dengan kegiatan tertentu</p>
            </div>
        </div>
        @endif

        {{-- Display Settings --}}
        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-bold">{{ $adType === 'link' ? '3' : '2' }}</span>
                Pengaturan Tampilan
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Durasi (detik) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration" value="{{ old('duration', $popupAd->duration ?? 5) }}" class="form-input" min="3" max="60" required>
                    <p class="text-xs text-gray-400 mt-1">Berapa lama popup ditampilkan (3-60 detik)</p>
                    @error('duration') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Prioritas</label>
                    <input type="number" name="priority" value="{{ old('priority', $popupAd->priority ?? 0) }}" class="form-input" min="0">
                    <p class="text-xs text-gray-400 mt-1">0 = tertinggi. Makin kecil, makin dulu tampil</p>
                    @error('priority') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Mulai Tampil</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $popupAd->start_date?->format('Y-m-d') ?? '') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Selesai Tampil</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $popupAd->end_date?->format('Y-m-d') ?? '') }}" class="form-input">
                </div>
            </div>
            <p class="text-xs text-gray-400">Kosongkan tanggal untuk tampil tanpa batas waktu</p>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600" {{ old('is_active', $popupAd->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Aktifkan popup ini</span>
            </label>
        </div>

        {{-- Submit --}}
        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6">Simpan</button>
            <a href="{{ route('admin.media-it.popup-ads.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
