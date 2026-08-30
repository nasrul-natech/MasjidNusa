@extends('layouts.admin.media-it')

@section('title', 'Dashboard Media IT')
@section('page-title', 'Dashboard Media IT')
@section('page-subtitle', 'Ringkasan konten & kegiatan')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card variant="filled" label="Kegiatan Mendatang" :value="$stats['upcoming_activities']" iconName="calendar" color="emerald" :href="route('admin.media-it.activities.index')" />
    <x-stat-card variant="filled" label="Total Artikel" :value="$stats['total_articles']" iconName="document" color="blue" :href="route('admin.media-it.articles.index')" />
    <x-stat-card variant="filled" label="Banner Aktif" :value="$stats['active_banners']" iconName="photo" color="purple" :href="route('admin.media-it.banners.index')" />
    <x-stat-card variant="filled" label="Draft Konten" :value="$stats['draft_content']" iconName="archive" color="amber" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card overflow-hidden">
        <div class="card-header bg-gradient-to-r from-emerald-700 to-teal-600 border-0 flex justify-between items-center">
            <h3 class="font-semibold text-white flex items-center gap-2">
                @include('components.icons.calendar', ['class' => 'w-4 h-4'])
                Kegiatan Terbaru
            </h3>
            <a href="{{ route('admin.media-it.activities.create') }}" class="text-xs text-white/90 hover:text-white font-medium bg-white/20 px-2.5 py-1 rounded-lg">+ Tambah</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentActivities as $activity)
            <div class="p-4 flex justify-between items-start hover:bg-gray-50 transition-colors">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                        @include('components.icons.calendar', ['class' => 'w-4 h-4 text-emerald-700'])
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium text-sm truncate">{{ $activity->title }}</p>
                        <p class="text-xs text-gray-400">{{ $activity->start_date?->format('d M Y') }}</p>
                    </div>
                </div>
                <x-badge type="{{ $activity->status === 'publish' ? 'success' : ($activity->status === 'draft' ? 'yellow' : 'gray') }}">{{ ucfirst($activity->status) }}</x-badge>
            </div>
            @empty
            <p class="p-6 text-sm text-gray-500 text-center">Belum ada kegiatan</p>
            @endforelse
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="card-header bg-gradient-to-r from-blue-600 to-indigo-600 border-0 flex justify-between items-center">
            <h3 class="font-semibold text-white flex items-center gap-2">
                @include('components.icons.document', ['class' => 'w-4 h-4'])
                Artikel Terbaru
            </h3>
            <a href="{{ route('admin.media-it.articles.create') }}" class="text-xs text-white/90 hover:text-white font-medium bg-white/20 px-2.5 py-1 rounded-lg">+ Tambah</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentArticles as $article)
            <div class="p-4 flex justify-between items-start hover:bg-gray-50 transition-colors">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                        @include('components.icons.document', ['class' => 'w-4 h-4 text-blue-700'])
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium text-sm truncate">{{ $article->title }}</p>
                        <p class="text-xs text-gray-400">{{ $article->author?->name }} · {{ $article->created_at->format('d M Y') }}</p>
                    </div>
                </div>
                <x-badge type="{{ $article->status === 'publish' ? 'success' : 'yellow' }}">{{ ucfirst($article->status) }}</x-badge>
            </div>
            @empty
            <p class="p-6 text-sm text-gray-500 text-center">Belum ada artikel</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
