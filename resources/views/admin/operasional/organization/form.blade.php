@extends('layouts.admin.operasional')
@php($organization = $organization ?? new \App\Models\OrganizationStructure)

@section('title', $organization->exists ? 'Edit Anggota' : 'Tambah Anggota Organisasi')
@section('page-title', $organization->exists ? 'Edit Anggota Struktur' : 'Tambah Anggota Struktur Organisasi')

@section('content')
<div class="max-w-xl">
    <form action="{{ $organization->exists ? route('admin.operasional.organization.update', $organization->id) : route('admin.operasional.organization.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @if($organization->exists) @method('PUT') @endif

        <div class="card p-5 space-y-4">
            <div>
                <label class="form-label">Periode Kepengurusan <span class="text-red-500">*</span></label>
                <select name="management_history_id" class="form-input" required>
                    <option value="">-- Pilih Periode --</option>
                    @foreach($managements as $management)
                    <option value="{{ $management->id }}" {{ old('management_history_id', $organization->management_history_id ?? '') == $management->id ? 'selected' : '' }}>
                        {{ $management->title }} ({{ $management->start_year }} - {{ $management->end_year ?? 'Sekarang' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $organization->name ?? '') }}" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="position" value="{{ old('position', $organization->position ?? '') }}" class="form-input" required placeholder="Contoh: Ketua, Sekretaris, ...">
            </div>

            <div>
                <label class="form-label">Urutan</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $organization->sort_order ?? 0) }}" class="form-input" min="0">
            </div>

            @if($organization->exists && $organization->photo)
            <div>
                <p class="text-xs text-gray-500 mb-1">Foto Saat Ini</p>
                <img src="{{ asset('storage/' . $organization->photo) }}" class="h-20 w-20 rounded-full object-cover">
            </div>
            @endif

            <div>
                <label class="form-label">Foto</label>
                <input type="file" name="photo" accept="image/*" class="form-input">
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6">Simpan</button>
            <a href="{{ route('admin.operasional.organization.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
