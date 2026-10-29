@extends('layouts.admin.operasional')

@section('title', 'Kategori Doa')
@section('page-title', 'Kategori Doa')

@section('content')
<div class="max-w-2xl">
    <div class="flex justify-between items-center mb-5">
        <p class="text-sm text-gray-500">Kelola kategori untuk kumpulan doa</p>
        <a href="{{ route('admin.operasional.doa-category.create') }}" class="btn-primary text-sm">+ Tambah Kategori</a>
    </div>

    <div class="space-y-3">
        @forelse($categories as $cat)
        <div class="card p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center text-lg">
                    {{ ['book'=>'📖','kaaba'=>'🕋','plane'=>'✈️','pray'=>'🤲','hadith'=>'📜','moon'=>'🌙','star'=>'⭐','zikir'=>'📿'][$cat->icon] ?? '📖' }}
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 text-sm">{{ $cat->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $cat->duas_count }} doa &middot; Urutan: {{ $cat->sort_order }}</p>
                </div>
            </div>
            <div class="action-group">
                <a href="{{ route('admin.operasional.doa-category.edit', $cat->id) }}" class="btn-action-blue">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form action="{{ route('admin.operasional.doa-category.destroy', $cat->id) }}" method="POST" data-confirm="Hapus kategori ini dan semua doa di dalamnya?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-action-red">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="card p-8 text-center text-gray-500">Belum ada kategori doa</div>
        @endforelse
    </div>

    <div class="mt-5">
        <a href="{{ route('admin.operasional.doa.index') }}" class="btn-secondary text-sm">&larr; Kembali ke Daftar Doa</a>
    </div>
</div>
@endsection
