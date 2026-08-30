@extends('layouts.admin.media-it')
@php($activity = $activity ?? new \App\Models\Activity)

@section('title', $activity->exists ? 'Edit Kegiatan' : 'Tambah Kegiatan')
@section('page-title', $activity->exists ? 'Edit Kegiatan' : 'Tambah Kegiatan')

@section('content')
<form action="{{ $activity->exists ? route('admin.media-it.activities.update', $activity->id) : route('admin.media-it.activities.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($activity->exists) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-5 space-y-4">
                <div>
                    <label class="form-label">Judul Kegiatan <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $activity->title ?? '') }}" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Deskripsi Singkat</label>
                    <textarea name="description" class="form-input" rows="3">{{ old('description', $activity->description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="form-label">Konten Lengkap</label>
                    <textarea name="content" class="form-input" rows="8" id="content-editor">{{ old('content', $activity->content ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card p-5 space-y-4">
                <h3 class="font-semibold text-gray-800 text-sm">Pengaturan</h3>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="draft" {{ old('status', $activity->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="publish" {{ old('status', $activity->status ?? '') === 'publish' ? 'selected' : '' }}>Publish</option>
                        <option value="selesai" {{ old('status', $activity->status ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="arsip" {{ old('status', $activity->status ?? '') === 'arsip' ? 'selected' : '' }}>Arsip</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-input">
                        <option value="">-- Tanpa Kategori --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $activity->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location', $activity->location ?? '') }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="start_date" value="{{ old('start_date', $activity->exists ? $activity->start_date?->format('Y-m-d\TH:i') : '') }}" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="datetime-local" name="end_date" value="{{ old('end_date', $activity->exists ? $activity->end_date?->format('Y-m-d\TH:i') : '') }}" class="form-input">
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" class="rounded text-emerald-600" {{ old('is_featured', $activity->is_featured ?? false) ? 'checked' : '' }}>
                    <span class="text-sm">Unggulan (tampil di beranda)</span>
                </label>
            </div>

            <div class="card p-5 space-y-3">
                <h3 class="font-semibold text-gray-800 text-sm">Banner</h3>
                @if($activity->exists && $activity->banner)
                <img src="{{ asset('storage/' . $activity->banner) }}" class="w-full h-24 object-cover rounded-lg">
                @endif
                <input type="file" name="banner" accept="image/*" class="form-input">
                <p class="text-xs text-gray-400">Disarankan: 1200x630px, max 4MB</p>
            </div>

            <div class="flex flex-col gap-2">
                <button type="submit" class="btn-primary w-full">Simpan</button>
                <a href="{{ route('admin.media-it.activities.index') }}" class="btn-secondary w-full text-center">Batal</a>
            </div>
        </div>
    </div>
</form>
@endsection
