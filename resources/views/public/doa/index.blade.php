@extends('layouts.public')

@section('title', 'Kumpulan Doa')

@push('styles')
<style>
.doa-arabic {
    font-family: 'Traditional Arabic', 'Scheherazade New', 'Amiri', serif !important;
    font-size: 1.35rem !important;
    line-height: 2.2 !important;
}
.doa-arabic-detail {
    font-family: 'Traditional Arabic', 'Scheherazade New', 'Amiri', serif !important;
    font-size: 1.6rem !important;
    line-height: 2.5 !important;
}
@@media (max-width: 640px) {
    .doa-arabic {
        font-size: 1.55rem !important;
    }
    .doa-arabic-detail {
        font-size: 1.8rem !important;
    }
}
</style>
@endpush

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="text-center mb-5">
        <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <span class="text-2xl">🤲</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900">Kumpulan Doa & Dzikir</h1>
        <p class="text-sm text-gray-500 mt-1">Doa-doa pilihan beserta sumber yang shahih</p>
    </div>

    {{-- 2 Tabs: Kategori & Doa Saya --}}
    <div class="flex bg-gray-100 rounded-xl p-1 mb-5">
        <a href="{{ route('doa.index', ['tab' => 'kategori']) }}"
            class="flex-1 py-2 text-center text-sm font-semibold rounded-lg transition-colors {{ $tab !== 'favorites' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500' }}">
            Kategori
        </a>
        <a href="{{ route('doa.index', ['tab' => 'favorites']) }}"
            class="flex-1 py-2 text-center text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-1.5 {{ $tab === 'favorites' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500' }}">
            <svg class="w-4 h-4 {{ $tab === 'favorites' ? 'text-amber-500' : '' }}" fill="{{ $tab === 'favorites' ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            Doa Saya
        </a>
    </div>

    @if($tab === 'favorites')
    {{-- ============ TAB: DOA SAYA (FAVORITES) ============ --}}
    @guest
    <div class="card p-10 text-center">
        <div class="text-5xl mb-3">🔒</div>
        <p class="text-gray-700 font-semibold">Login untuk melihat Doa Saya</p>
        <p class="text-gray-400 text-sm mt-1">Pin doa favorit agar mudah diakses kembali</p>
        <a href="{{ route('login') }}" class="inline-block mt-4 px-6 py-2 bg-emerald-700 text-white text-sm font-semibold rounded-lg hover:bg-emerald-800 transition-colors">Login</a>
    </div>
    @else
        @forelse($duas as $doa)
        @include('public.doa._card', ['doa' => $doa, 'favoritedIds' => $favoritedIds])
        @empty
        <div class="text-center py-16">
            <div class="text-5xl mb-3">⭐</div>
            <p class="text-gray-500 font-medium">Belum ada doa tersimpan</p>
            <p class="text-gray-400 text-sm mt-1">Simpan doa favorit dari daftar kategori</p>
            <a href="{{ route('doa.index', ['tab' => 'kategori']) }}" class="inline-block mt-3 text-sm text-emerald-600 font-semibold hover:underline">Lihat Kategori</a>
        </div>
        @endforelse
    @endguest

    @elseif($categorySlug)
    {{-- ============ LIST DOA PER KATEGORI ============ --}}
    @php $activeCategory = $categories->firstWhere('slug', $categorySlug); @endphp
    <div class="mb-4">
        <a href="{{ route('doa.index') }}" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali ke Kategori
        </a>
        @if($activeCategory)
        <h2 class="text-lg font-bold text-gray-900">{{ $activeCategory->name }}</h2>
        <p class="text-xs text-gray-500 mt-0.5">{{ $activeCategory->published_duas_count }} doa tersedia</p>
        @endif
    </div>

    @forelse($duas as $doa)
    @include('public.doa._card', ['doa' => $doa, 'favoritedIds' => $favoritedIds])
    @empty
    <div class="text-center py-16">
        <div class="text-5xl mb-3">🤲</div>
        <p class="text-gray-500 font-medium">Belum ada doa dalam kategori ini</p>
    </div>
    @endforelse

    @else
    {{-- ============ TAB: KATEGORI (GRID ICONS) ============ --}}
    <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @foreach($categories as $cat)
        @php
            $iconMap = [
                'book'   => ['emoji' => '📖', 'bg' => 'bg-blue-50', 'border' => 'border-blue-100'],
                'kaaba'  => ['emoji' => '🕋', 'bg' => 'bg-amber-50', 'border' => 'border-amber-100'],
                'plane'  => ['emoji' => '✈️', 'bg' => 'bg-sky-50', 'border' => 'border-sky-100'],
                'pray'   => ['emoji' => '🤲', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-100'],
                'hadith' => ['emoji' => '📜', 'bg' => 'bg-orange-50', 'border' => 'border-orange-100'],
                'moon'   => ['emoji' => '🌙', 'bg' => 'bg-indigo-50', 'border' => 'border-indigo-100'],
                'star'   => ['emoji' => '⭐', 'bg' => 'bg-yellow-50', 'border' => 'border-yellow-100'],
                'zikir'  => ['emoji' => '📿', 'bg' => 'bg-teal-50', 'border' => 'border-teal-100'],
            ];
            $ic = $iconMap[$cat->icon] ?? $iconMap['book'];
        @endphp
        <a href="{{ route('doa.index', ['category' => $cat->slug]) }}"
            class="flex flex-col items-center gap-2 p-4 rounded-2xl border {{ $ic['border'] }} {{ $ic['bg'] }} hover:shadow-md transition-shadow text-center group">
            <div class="text-3xl group-hover:scale-110 transition-transform">{{ $ic['emoji'] }}</div>
            <div>
                <h3 class="text-xs font-bold text-gray-800 leading-tight">{{ $cat->name }}</h3>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $cat->published_duas_count }} doa</p>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Quick Preview: Recent / Popular --}}
    @php
        $recentDuas = \App\Models\Doa::published()->with('category')->latest()->limit(5)->get();
    @endphp
    @if($recentDuas->count())
    <div class="mt-6">
        <h2 class="font-bold text-gray-900 text-sm mb-3">Doa Terbaru</h2>
        @foreach($recentDuas as $doa)
        @include('public.doa._card', ['doa' => $doa, 'favoritedIds' => $favoritedIds])
        @endforeach
    </div>
    @endif

    @endif
</div>

@auth
<script>
function toggleFav(doaId, btn) {
    fetch('/doa/' + doaId + '/favorite', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        const svg = btn.querySelector('svg');
        const span = btn.querySelector('span');
        if (data.favorited) {
            btn.classList.remove('text-gray-400');
            btn.classList.add('text-amber-500');
            svg.setAttribute('fill', 'currentColor');
            span.textContent = 'Tersimpan';
        } else {
            btn.classList.remove('text-amber-500');
            btn.classList.add('text-gray-400');
            svg.setAttribute('fill', 'none');
            span.textContent = 'Simpan';
        }
    });
}
</script>
@endauth
@endsection
