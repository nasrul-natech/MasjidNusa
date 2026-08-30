@extends('layouts.admin.operasional')

@section('title', 'Kepengurusan')
@section('page-title', 'Kepengurusan Masjid')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('admin.operasional.management-history.create') }}" class="btn-primary">+ Tambah Periode</a>
</div>

<div class="space-y-3">
    @forelse($managements as $management)
    <div class="card p-4">
        <div class="flex justify-between items-start">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <p class="font-semibold text-gray-900">{{ $management->title }}</p>
                    @if($management->is_current)
                    <x-badge type="success">Saat Ini</x-badge>
                    @endif
                </div>
                <p class="text-sm text-gray-500">Periode: {{ $management->start_year }} - {{ $management->end_year ?? 'Sekarang' }}</p>
                @if($management->description)
                <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $management->description }}</p>
                @endif
            </div>
            <div class="action-group shrink-0 ml-4">
                <a href="{{ route('admin.operasional.management-history.edit', $management->id) }}" class="btn-action-blue">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form action="{{ route('admin.operasional.management-history.destroy', $management->id) }}" method="POST" onsubmit="return confirm('Hapus data kepengurusan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-action-red">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-8 text-center text-gray-500">Belum ada data kepengurusan</div>
    @endforelse
</div>

<div class="mt-4">{{ $managements->links() }}</div>
@endsection
