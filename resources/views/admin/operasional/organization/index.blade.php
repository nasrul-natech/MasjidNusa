@extends('layouts.admin.operasional')

@section('title', 'Struktur Organisasi')
@section('page-title', 'Struktur Organisasi')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('admin.operasional.organization.create') }}" class="btn-primary">+ Tambah Anggota</a>
</div>

@forelse($managements as $management)
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <h3 class="font-semibold text-gray-700">{{ $management->title }}</h3>
        @if($management->is_current)
        <x-badge type="success">Aktif</x-badge>
        @endif
        <span class="text-xs text-gray-400">{{ $management->start_year }} - {{ $management->end_year ?? 'Sekarang' }}</span>
    </div>
    <x-admin.table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Urutan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($management->allStructures->sortBy('sort_order') as $org)
                <tr>
                    <td data-label="Nama">
                        <div class="flex items-center gap-2">
                            @if($org->photo)
                            <img src="{{ asset('storage/' . $org->photo) }}" class="w-8 h-8 rounded-full object-cover">
                            @else
                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-xs font-bold">{{ substr($org->name, 0, 1) }}</div>
                            @endif
                            <span class="font-medium">{{ $org->name }}</span>
                        </div>
                    </td>
                    <td data-label="Jabatan">{{ $org->position }}</td>
                    <td data-label="Urutan" class="text-center">{{ $org->sort_order }}</td>
                    <td data-label="Aksi">
                        <div class="action-group">
                            <a href="{{ route('admin.operasional.organization.edit', $org->id) }}" class="btn-action-blue">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <form action="{{ route('admin.operasional.organization.destroy', $org->id) }}" method="POST" data-confirm="Hapus anggota ini?">
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
                <tr><td colspan="4" class="text-center py-4 text-gray-400 text-sm">Belum ada anggota di periode ini</td></tr>
                @endforelse
            </tbody>
    </x-admin.table>
</div>
@empty
<div class="card p-8 text-center text-gray-500">
    <p>Belum ada data. Tambah periode kepengurusan terlebih dahulu.</p>
    <a href="{{ route('admin.operasional.management-history.create') }}" class="text-emerald-600 hover:underline text-sm mt-2 inline-block">+ Tambah Periode</a>
</div>
@endforelse
@endsection
