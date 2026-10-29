@extends('layouts.admin.media-it')

@section('title', 'Kelola Artikel')
@section('page-title', 'Kelola Artikel / Dakwah')

@section('content')
<div class="flex flex-wrap justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2">
        <select name="status" class="form-input w-auto text-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="publish" {{ request('status') === 'publish' ? 'selected' : '' }}>Published</option>
            <option value="arsip" {{ request('status') === 'arsip' ? 'selected' : '' }}>Arsip</option>
        </select>
    </form>
    <a href="{{ route('admin.media-it.articles.create') }}" class="btn-primary">+ Tulis Artikel</a>
</div>

<x-admin.table>
    <thead>
        <tr>
            <th>Artikel</th>
            <th>Kategori</th>
            <th>Penulis</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($articles as $article)
        <tr>
            <td data-label="Artikel">
                <div class="flex items-center gap-2">
                    @if($article->thumbnail)
                    <img src="{{ asset('storage/' . $article->thumbnail) }}" class="w-10 h-10 rounded-lg object-cover shrink-0">
                    @endif
                    <p class="font-medium text-sm line-clamp-2">{{ $article->title }}</p>
                </div>
            </td>
            <td data-label="Kategori" class="text-sm">{{ $article->category?->name ?? '-' }}</td>
            <td data-label="Penulis" class="text-sm">{{ $article->author?->name }}</td>
            <td data-label="Status">
                @php $statusColors = ['draft'=>'yellow','publish'=>'success','arsip'=>'gray']; @endphp
                <x-badge type="{{ $statusColors[$article->status] ?? 'gray' }}">{{ ucfirst($article->status) }}</x-badge>
            </td>
            <td data-label="Tanggal" class="text-xs text-gray-500">{{ $article->created_at->format('d/m/Y') }}</td>
            <td data-label="Aksi">
                <div class="action-group flex-wrap">
                    <a href="{{ route('admin.media-it.articles.edit', $article->id) }}" class="btn-action-blue">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    @if($article->status === 'draft')
                    <form action="{{ route('admin.media-it.articles.publish', $article->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-action-green">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Publish
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('admin.media-it.articles.destroy', $article->id) }}" method="POST" data-confirm="Hapus artikel ini?">
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
        <tr><td colspan="6" class="text-center py-8 text-gray-500">Belum ada artikel</td></tr>
        @endforelse
    </tbody>
</x-admin.table>

<div class="mt-4">{{ $articles->withQueryString()->links() }}</div>
@endsection
