@extends('layouts.admin.media-it')

@section('title', 'Kelola Kegiatan')
@section('page-title', 'Kelola Kegiatan')

@section('content')
<div class="flex flex-wrap justify-between gap-3 mb-5">
    <div class="flex gap-2">
        <a href="{{ route('admin.media-it.activities.index', ['filter' => 'upcoming']) }}" class="text-sm px-4 py-2 rounded-lg {{ $filter === 'upcoming' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">Mendatang</a>
        <a href="{{ route('admin.media-it.activities.index', ['filter' => 'past']) }}" class="text-sm px-4 py-2 rounded-lg {{ $filter === 'past' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">Lalu</a>
    </div>
    <a href="{{ route('admin.media-it.activities.create') }}" class="btn-primary">+ Tambah Kegiatan</a>
</div>

<x-admin.table>
    <thead>
        <tr>
            <th>Kegiatan</th>
            <th>Kategori</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($activities as $activity)
        <tr>
            <td data-label="Kegiatan">
                <div class="flex items-center gap-2">
                    @if($activity->banner)
                    <img src="{{ asset('storage/' . $activity->banner) }}" class="w-10 h-10 rounded-lg object-cover shrink-0">
                    @endif
                    <div>
                        <p class="font-medium text-sm">{{ $activity->title }}</p>
                        @if($activity->location)
                        <p class="text-xs text-gray-400">📍 {{ $activity->location }}</p>
                        @endif
                    </div>
                </div>
            </td>
            <td data-label="Kategori" class="text-sm">{{ $activity->category?->name ?? '-' }}</td>
            <td data-label="Tanggal" class="text-xs">{{ $activity->start_date?->format('d M Y') }}</td>
            <td data-label="Status">
                @php $statusColors = ['draft'=>'yellow','publish'=>'success','selesai'=>'blue','arsip'=>'gray']; @endphp
                <x-badge type="{{ $statusColors[$activity->status] ?? 'gray' }}">{{ ucfirst($activity->status) }}</x-badge>
            </td>
            <td data-label="Aksi">
                <div class="action-group flex-wrap">
                    <a href="{{ route('admin.media-it.activities.edit', $activity->id) }}" class="btn-action-blue">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    @if($activity->status === 'draft')
                    <form action="{{ route('admin.media-it.activities.publish', $activity->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-action-green">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Publish
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('admin.media-it.activities.destroy', $activity->id) }}" method="POST" data-confirm="Hapus kegiatan ini?">
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
        <tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada kegiatan</td></tr>
        @endforelse
    </tbody>
</x-admin.table>

<div class="mt-4">{{ $activities->withQueryString()->links() }}</div>
@endsection
