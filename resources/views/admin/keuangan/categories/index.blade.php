@extends('layouts.admin.keuangan')

@section('title', 'Kategori Donasi')
@section('page-title', 'Kategori Donasi')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {{-- Add Form --}}
    <div class="card p-5 lg:p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                @include('components.icons.archive', ['class' => 'w-5 h-5 text-emerald-700'])
            </div>
            <div>
                <h3 class="font-bold text-gray-900">Tambah Kategori Baru</h3>
                <p class="text-xs text-gray-500">Kelompokkan program donasi</p>
            </div>
        </div>
        <form action="{{ route('admin.keuangan.donation-categories.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="form-label">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Contoh: Operasional Masjid, Pembangunan, Sosial" required>
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label">Deskripsi</label>
                <input type="text" name="description" value="{{ old('description') }}" class="form-input" placeholder="Keterangan singkat kategori ini">
            </div>
            <button type="submit" class="btn-primary text-sm">Tambah Kategori</button>
        </form>
    </div>

    {{-- Category List --}}
    <div class="space-y-3">
        @forelse($categories as $category)
        <div class="card p-4" x-data="{ editing: false }">
            <div x-show="!editing" class="flex items-center justify-between gap-3">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                        @include('components.icons.archive', ['class' => 'w-4 h-4 text-blue-700'])
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                        @if($category->description)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $category->description }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $category->campaigns_count }} program donasi</p>
                    </div>
                </div>
                <div class="action-group shrink-0">
                    <button @click="editing = true" class="btn-action-blue">Edit</button>
                    @if($category->campaigns_count === 0)
                    <form action="{{ route('admin.keuangan.donation-categories.destroy', $category->id) }}" method="POST" data-confirm="Hapus kategori ini?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-action-red">Hapus</button>
                    </form>
                    @else
                    <span class="btn-action-gray">Hapus</span>
                    @endif
                </div>
            </div>

            <div x-show="editing" x-transition>
                <form action="{{ route('admin.keuangan.donation-categories.update', $category->id) }}" method="POST" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="form-label text-xs">Nama</label>
                        <input type="text" name="name" value="{{ $category->name }}" class="form-input text-sm" required>
                    </div>
                    <div>
                        <label class="form-label text-xs">Deskripsi</label>
                        <input type="text" name="description" value="{{ $category->description }}" class="form-input text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary text-xs px-4">Simpan</button>
                        <button type="button" @click="editing = false" class="btn-secondary text-xs px-4">Batal</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="card p-8 text-center border-2 border-dashed border-gray-200">
            <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                @include('components.icons.archive', ['class' => 'w-6 h-6 text-gray-400'])
            </div>
            <p class="text-gray-500 font-medium">Belum ada kategori donasi</p>
            <p class="text-gray-400 text-sm mt-1">Tambahkan kategori di panel kiri</p>
        </div>
        @endforelse
    </div>
</div>

<div class="mt-6">
    <a href="{{ route('admin.keuangan.campaigns.index') }}" class="btn-secondary text-sm">&larr; Kembali ke Program Donasi</a>
</div>
@endsection
