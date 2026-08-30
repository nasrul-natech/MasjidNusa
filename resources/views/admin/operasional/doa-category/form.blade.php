@extends('layouts.admin.operasional')
@php($category = $category ?? null)

@section('title', $category ? 'Edit Kategori Doa' : 'Tambah Kategori Doa')
@section('page-title', $category ? 'Edit Kategori Doa' : 'Tambah Kategori Doa')

@section('content')
<div class="max-w-lg">
    <form action="{{ $category ? route('admin.operasional.doa-category.update', $category->id) : route('admin.operasional.doa-category.store') }}"
          method="POST" class="card p-5 space-y-4">
        @csrf
        @if($category) @method('PUT') @endif

        <div>
            <label class="form-label">Nama Kategori <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" class="form-input" required placeholder="Contoh: Doa Harian">
            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Icon</label>
            <select name="icon" class="form-input">
                @foreach(['book' => '📖 Buku', 'kaaba' => '🕋 Ka\'bah', 'plane' => '✈️ Bepergian', 'pray' => '🤲 Berdoa', 'hadith' => '📜 Hadits', 'moon' => '🌙 Bulan', 'star' => '⭐ Bintang', 'zikir' => '📿 Zikir'] as $val => $label)
                <option value="{{ $val }}" {{ old('icon', $category->icon ?? 'book') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Urutan</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-input" min="0">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Simpan</button>
            <a href="{{ route('admin.operasional.doa-category.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
