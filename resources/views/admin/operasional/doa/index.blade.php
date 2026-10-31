@extends('layouts.admin.operasional')

@section('title', 'Kumpulan Doa')
@section('page-title', 'Kumpulan Doa')

@section('content')
{{-- Action Buttons --}}
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('admin.operasional.doa.create') }}" class="btn-primary text-sm">+ Tambah Doa</a>
    <a href="{{ route('admin.operasional.doa-category.index') }}" class="btn-secondary text-sm">Kelola Kategori</a>
</div>

{{-- Filter --}}
<x-admin.table-filter :action="route('admin.operasional.doa.index')">
    <div class="filter-field-wide">
        <label class="form-label text-xs">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" class="form-input text-sm" placeholder="Judul doa...">
    </div>
    <div class="filter-field">
        <label class="form-label text-xs">Kategori</label>
        <select name="category" class="form-input text-sm">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-field">
        <label class="form-label text-xs">Status</label>
        <select name="status" class="form-input text-sm">
            <option value="">Semua</option>
            <option value="publish" {{ request('status') === 'publish' ? 'selected' : '' }}>Publish</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
        </select>
    </div>
</x-admin.table-filter>

{{-- Doa List --}}
<x-admin.table title="Daftar Doa" badge="Konten">
    <thead>
        <tr>
            <th class="w-10">#</th>
            <x-admin.sortable-th column="title" label="Judul" />
            <th>Kategori</th>
            <th>Sumber</th>
            <x-admin.sortable-th column="status" label="Status" />
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($duas as $doa)
        <tr>
            <td class="text-center text-gray-500" data-label="#">{{ $doa->sort_order }}</td>
            <td data-label="Judul">
                <div class="font-semibold text-gray-900">{{ $doa->title }}</div>
                @if($doa->arabic)
                <p class="text-xs text-gray-400 mt-0.5 font-arabic" dir="rtl">{{ Str::limit($doa->arabic, 60) }}</p>
                @endif
            </td>
            <td data-label="Kategori"><x-badge type="info">{{ $doa->category->name ?? '-' }}</x-badge></td>
            <td class="text-xs text-gray-500" data-label="Sumber">{{ Str::limit($doa->source, 30) ?: '-' }}</td>
            <td data-label="Status"><x-badge type="{{ $doa->status === 'publish' ? 'success' : 'gray' }}">{{ ucfirst($doa->status) }}</x-badge></td>
            <td data-label="Aksi">
                <div class="action-group">
                    <a href="{{ route('admin.operasional.doa.edit', $doa->id) }}" class="btn-action-blue">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <form action="{{ route('admin.operasional.doa.destroy', $doa->id) }}" method="POST" data-confirm="Hapus doa ini?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-action-red">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-gray-500 py-8">
                Belum ada doa. <a href="{{ route('admin.operasional.doa.create') }}" class="text-emerald-600 hover:underline">Tambah doa pertama</a>
            </td>
        </tr>
        @endforelse
    </tbody>
</x-admin.table>

{{ $duas->links() }}
@endsection
