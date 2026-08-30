@extends('layouts.admin.operasional')
@php($history = $history ?? new \App\Models\MosqueHistory)

@section('title', $history->exists ? 'Edit Sejarah' : 'Tambah Sejarah')
@section('page-title', $history->exists ? 'Edit Sejarah Masjid' : 'Tambah Sejarah Masjid')

@section('content')
<div class="max-w-xl">
    <form action="{{ $history->exists ? route('admin.operasional.mosque-history.update', $history->id) : route('admin.operasional.mosque-history.store') }}" method="POST" class="space-y-4">
        @csrf
        @if($history->exists) @method('PUT') @endif

        <div class="card p-5 space-y-4">
            <div>
                <label class="form-label">Judul <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $history->title ?? '') }}" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Tanggal Kejadian</label>
                <input type="date" name="event_date" value="{{ old('event_date', $history->exists ? $history->event_date?->format('Y-m-d') : '') }}" class="form-input">
            </div>

            <div>
                <label class="form-label">Konten / Cerita <span class="text-red-500">*</span></label>
                <textarea name="content" class="form-input" rows="6" required>{{ old('content', $history->content ?? '') }}</textarea>
            </div>

            <div>
                <label class="form-label">Urutan Tampil</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $history->sort_order ?? 0) }}" class="form-input" min="0">
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6">Simpan</button>
            <a href="{{ route('admin.operasional.mosque-history.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
