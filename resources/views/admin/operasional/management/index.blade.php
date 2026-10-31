@extends('layouts.admin.operasional')

@section('title', 'Kepengurusan')
@section('page-title', 'Kepengurusan Masjid')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('admin.operasional.management-history.create') }}" class="btn-primary">+ Tambah Pengurus</a>
</div>

@forelse($managements as $periodLabel => $members)
<div class="mb-6">
    <h3 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Periode {{ $periodLabel }}</h3>
    <x-admin.table title="Kepengurusan">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($members as $member)
                <tr>
                    <td data-label="Nama">
                        <div class="flex items-center gap-2">
                            @if($member->photo)
                            <img src="{{ $member->photo_url }}" class="w-8 h-8 rounded-full object-cover">
                            @else
                            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-xs font-bold">{{ substr($member->name, 0, 1) }}</div>
                            @endif
                            <span class="font-medium">{{ $member->name }}</span>
                        </div>
                    </td>
                    <td data-label="Jabatan">{{ $member->position }}</td>
                    <td data-label="Status"><x-badge type="{{ $member->is_active ? 'success' : 'gray' }}">{{ $member->is_active ? 'Aktif' : 'Tidak Aktif' }}</x-badge></td>
                    <td data-label="Aksi">
                        <div class="action-group">
                            <a href="{{ route('admin.operasional.management-history.edit', $member->id) }}" class="btn-action-blue">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <form action="{{ route('admin.operasional.management-history.destroy', $member->id) }}" method="POST" data-confirm="Hapus pengurus ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-action-red">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
    </x-admin.table>
</div>
@empty
<div class="card p-8 text-center text-gray-500">Belum ada data kepengurusan</div>
@endforelse
@endsection
