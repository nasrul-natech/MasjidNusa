@extends('layouts.admin.operasional')

@section('title', isset($management) ? 'Edit Pengurus' : 'Tambah Pengurus')
@section('page-title', isset($management) ? 'Edit Pengurus' : 'Tambah Pengurus')

@section('content')
<div class="max-w-xl">
        <form action="{{ isset($management) ? route('admin.operasional.management-history.update', $management->id) : route('admin.operasional.management-history.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if(isset($management)) @method('PUT') @endif

        <div class="card p-5 space-y-4">
            <div>
                <label class="form-label">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $management->name ?? '') }}" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="position" value="{{ old('position', $management->position ?? '') }}" class="form-input" required placeholder="Contoh: Ketua Takmir">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Periode Mulai</label>
                    <input type="number" name="period_start" value="{{ old('period_start', $management->period_start ?? '') }}" class="form-input" placeholder="2020">
                </div>
                <div>
                    <label class="form-label">Periode Akhir</label>
                    <input type="number" name="period_end" value="{{ old('period_end', $management->period_end ?? '') }}" class="form-input" placeholder="2025">
                </div>
            </div>

            <div>
                <label class="form-label">Urutan Tampil</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $management->sort_order ?? 0) }}" class="form-input" min="0">
            </div>

            <div>
                <label class="form-label">Catatan / Latar Belakang</label>
                <textarea name="bio" class="form-input" rows="3">{{ old('bio', $management->bio ?? '') }}</textarea>
            </div>

            @if(isset($management) && $management->photo)
            <div>
                <p class="text-xs text-gray-500 mb-1">Foto Saat Ini</p>
                <img src="{{ $management->photo_url }}" class="h-20 w-20 rounded-full object-cover">
            </div>
            @endif

            <div>
                <label class="form-label">Foto</label>
                <input type="file" name="photo" accept="image/*" class="form-input">
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600" {{ old('is_active', $management->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Aktif / Menjabat saat ini</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6">Simpan</button>
            <a href="{{ route('admin.operasional.management-history.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
