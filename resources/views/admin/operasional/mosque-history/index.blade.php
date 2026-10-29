@extends('layouts.admin.operasional')

@section('title', 'Sejarah Masjid')
@section('page-title', 'Sejarah Masjid')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('admin.operasional.mosque-history.create') }}" class="btn-primary">+ Tambah Sejarah</a>
</div>

<div class="space-y-3">
    @forelse($histories as $history)
    <div class="card p-4">
        <div class="flex justify-between items-start">
            <div class="flex gap-3">
                @if($history->event_date)
                <div class="w-14 h-14 rounded-xl bg-emerald-100 flex flex-col items-center justify-center text-emerald-700 font-bold text-xs shrink-0">
                    <span>{{ $history->event_date->format('d') }}</span>
                    <span>{{ $history->event_date->format('M') }}</span>
                    <span>{{ $history->event_date->format('Y') }}</span>
                </div>
                @endif
                <div>
                    <p class="font-semibold text-gray-900">{{ $history->title }}</p>
                    <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ Str::limit($history->content, 120) }}</p>
                </div>
            </div>
            <div class="action-group shrink-0 ml-4">
                <a href="{{ route('admin.operasional.mosque-history.edit', $history->id) }}" class="btn-action-blue">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form action="{{ route('admin.operasional.mosque-history.destroy', $history->id) }}" method="POST" data-confirm="Hapus sejarah ini?">
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
    <div class="card p-8 text-center text-gray-500">Belum ada data sejarah masjid</div>
    @endforelse
</div>
<div class="mt-4">{{ $histories->links() }}</div>
@endsection
