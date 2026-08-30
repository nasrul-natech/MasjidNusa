@extends('layouts.admin.operasional')
@php($management = $management ?? new \App\Models\ManagementHistory)

@section('title', $management->exists ? 'Edit Kepengurusan' : 'Tambah Kepengurusan')
@section('page-title', $management->exists ? 'Edit Periode Kepengurusan' : 'Tambah Periode Kepengurusan')

@section('content')
<div class="max-w-xl">
    <form action="{{ $management->exists ? route('admin.operasional.management-history.update', $management->id) : route('admin.operasional.management-history.store') }}" method="POST" class="space-y-4">
        @csrf
        @if($management->exists) @method('PUT') @endif

        <div class="card p-5 space-y-4">
            <div>
                <label class="form-label">Nama / Judul Kepengurusan <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $management->title ?? '') }}" class="form-input" required placeholder="Contoh: Takmir Masjid Periode 2020-2025">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Tahun Mulai <span class="text-red-500">*</span></label>
                    <input type="number" name="start_year" value="{{ old('start_year', $management->start_year ?? date('Y')) }}" class="form-input" required min="1900" max="2100">
                </div>
                <div>
                    <label class="form-label">Tahun Berakhir</label>
                    <input type="number" name="end_year" value="{{ old('end_year', $management->end_year ?? '') }}" class="form-input" min="1900" max="2100" placeholder="Kosongkan jika aktif">
                </div>
            </div>

            <div>
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-input" rows="3">{{ old('description', $management->description ?? '') }}</textarea>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_current" value="0">
                <input type="checkbox" name="is_current" value="1" class="rounded text-emerald-600" {{ old('is_current', $management->is_current ?? false) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Kepengurusan aktif saat ini</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6">Simpan</button>
            <a href="{{ route('admin.operasional.management-history.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
