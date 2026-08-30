@extends('layouts.public')

@section('title', $doa->title . ' - Kumpulan Doa')

@push('styles')
<style>
.doa-arabic-detail {
    font-family: 'Traditional Arabic', 'Scheherazade New', 'Amiri', serif !important;
    font-size: 1.6rem !important;
    line-height: 2.5 !important;
}
@@media (max-width: 640px) {
    .doa-arabic-detail {
        font-size: 1.8rem !important;
    }
}
</style>
@endpush

@section('content')
<div class="max-w-screen-md mx-auto px-4 py-6">

    {{-- Back navigation --}}
    <div class="mb-4">
        <a href="{{ route('doa.index', $doa->category ? ['category' => $doa->category->slug] : []) }}" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <div class="card p-5">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                @if($doa->category)
                <x-badge type="info">{{ $doa->category->name }}</x-badge>
                @endif
                @if($doa->source)
                <span class="text-xs text-gray-400">{{ $doa->source }}</span>
                @endif
            </div>
            @auth
            <button onclick="toggleFav({{ $doa->id }}, this)" id="fav-btn"
                class="flex items-center gap-1 text-sm font-medium transition-colors {{ $isFavorited ? 'text-amber-500' : 'text-gray-400 hover:text-amber-500' }}">
                <svg class="w-5 h-5" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <span>{{ $isFavorited ? 'Tersimpan' : 'Simpan' }}</span>
            </button>
            @endauth
        </div>

        <h1 class="text-xl font-bold text-gray-900 mb-5">{{ $doa->title }}</h1>

        {{-- Arabic --}}
        @if($doa->arabic)
        <div class="bg-emerald-50 rounded-xl p-5 mb-4 border border-emerald-100">
            <p class="doa-arabic-detail text-right leading-loose text-gray-900" dir="rtl">
                {{ $doa->arabic }}
            </p>
        </div>
        @endif

        {{-- Latin --}}
        @if($doa->latin)
        <div class="mb-4">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Transliterasi</h3>
            <p class="text-sm text-emerald-700 italic leading-relaxed">{{ $doa->latin }}</p>
        </div>
        @endif

        {{-- Translation --}}
        <div class="mb-4">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Terjemahan</h3>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $doa->translation }}</p>
        </div>

        {{-- Extended content --}}
        @if($doa->content_html || $doa->content_text)
        <div class="mt-5 pt-4 border-t border-gray-100">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Penjelasan</h3>
            @if($doa->content_html)
            <div class="prose prose-sm max-w-none text-gray-700">{!! $doa->content_html !!}</div>
            @else
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $doa->content_text }}</p>
            @endif
        </div>
        @endif

        {{-- Source badge --}}
        @if($doa->source)
        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <span class="text-xs text-gray-500">Sumber: <strong>{{ $doa->source }}</strong></span>
        </div>
        @endif
    </div>

    {{-- Related Duas --}}
    @if($related->count() > 0)
    <div class="mt-5">
        <h2 class="font-bold text-gray-900 text-sm mb-3">Doa Lainnya</h2>
        <div class="space-y-2">
            @foreach($related as $r)
            <a href="{{ route('doa.show', $r->slug) }}" class="card p-4 block hover:shadow-md transition-shadow">
                <h3 class="font-semibold text-gray-900 text-sm">{{ $r->title }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($r->translation, 80) }}</p>
            </a>
            @endforeach
        </div>
    </div>
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
