@extends('layouts.public')

@section('title', 'Al-Quran Digital')
@section('meta_description', 'Baca Al-Quran digital dengan tajweed, terjemahan multi bahasa, dan audio tilawah')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Amiri+Quran&family=Scheherazade+New:wght@400;700&family=Noto+Naskh+Arabic:wght@400;700&display=swap" rel="stylesheet">
<style>
.quran-arabic-preview {
    font-family: 'Amiri Quran', 'Scheherazade New', serif;
    font-size: 1.3rem;
    line-height: 2;
}
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="text-center mb-5">
        <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-sm">
            <svg class="w-8 h-8 text-emerald-700" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H6zm0 2h12v16H6V4zm2 2v2h8V6H8zm0 4v1h8v-1H8zm0 3v1h5v-1H8z"/></svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900">Al-Quran Digital</h1>
        <p class="text-sm text-gray-500 mt-1">Baca, dengar, dan pelajari Al-Quran dengan tajweed</p>
    </div>

    {{-- Last Read Card (Authenticated) --}}
    @auth
    @if($lastRead && $lastRead->surah)
    <a href="{{ route('quran.surah', $lastRead->surah->number) }}#ayat-{{ $lastRead->verse_number }}"
        class="block mb-5 p-4 rounded-2xl shadow-lg hover:shadow-xl transition-shadow"
        style="background: linear-gradient(to right, #047857, #0d9488); color: #fff;">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(255,255,255,0.2);">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H6zm0 2h12v16H6V4z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium" style="color: #a7f3d0;">Lanjut Baca</p>
                <p class="font-bold text-sm truncate" style="color: #fff;">{{ $lastRead->surah->name_latin }} - Ayat {{ $lastRead->verse_number }}</p>
            </div>
            <svg class="w-5 h-5 shrink-0" style="color: #a7f3d0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </a>
    @endif
    @endauth

    {{-- Last Read Card (Guest - from localStorage) --}}
    @guest
    <a href="#" id="last-read-guest" class="mb-5 p-4 rounded-2xl shadow-lg cursor-pointer"
        style="display:none; background: linear-gradient(to right, #047857, #0d9488); color: #fff;">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(255,255,255,0.2);">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H6zm0 2h12v16H6V4z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium" style="color: #a7f3d0;">Lanjut Baca</p>
                <p class="font-bold text-sm truncate" style="color: #fff;" id="last-read-guest-text"></p>
            </div>
            <svg class="w-5 h-5 shrink-0" style="color: #a7f3d0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </div>
    </a>
    @endguest

    {{-- Search Bar --}}
    <form action="{{ route('quran.search') }}" method="GET" class="mb-5">
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Cari ayat, terjemahan, atau transliterasi..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        </div>
    </form>

    {{-- Tabs --}}
    <div class="flex bg-gray-100 rounded-xl p-1 mb-5 overflow-x-auto scrollbar-hide">
        <a href="{{ route('quran.index', ['tab' => 'surah']) }}"
            class="flex-1 py-2 text-center text-sm font-semibold rounded-lg transition-colors whitespace-nowrap px-3 {{ ($tab ?? 'surah') === 'surah' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500' }}">
            Surah
        </a>
        <a href="{{ route('quran.index', ['tab' => 'juz']) }}"
            class="flex-1 py-2 text-center text-sm font-semibold rounded-lg transition-colors whitespace-nowrap px-3 {{ $tab === 'juz' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500' }}">
            Juz
        </a>
        @auth
        <a href="{{ route('quran.index', ['tab' => 'bookmark']) }}"
            class="flex-1 py-2 text-center text-sm font-semibold rounded-lg transition-colors whitespace-nowrap px-3 flex items-center justify-center gap-1 {{ $tab === 'bookmark' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500' }}">
            <svg class="w-3.5 h-3.5" fill="{{ $tab === 'bookmark' ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
            Pin
        </a>
        <a href="{{ route('quran.index', ['tab' => 'khatam']) }}"
            class="flex-1 py-2 text-center text-sm font-semibold rounded-lg transition-colors whitespace-nowrap px-3 {{ $tab === 'khatam' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500' }}">
            Khatam
        </a>
        @endauth
    </div>

    {{-- Search Results --}}
    @if(($tab ?? '') === 'search')
    <div class="mb-4">
        <a href="{{ route('quran.index') }}" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h2 class="text-sm font-bold text-gray-900">Hasil Pencarian "{{ $searchQuery }}"</h2>
        <p class="text-xs text-gray-500 mt-0.5">{{ ($searchResults ?? collect())->count() }} ayat ditemukan</p>
    </div>
    @if(($searchResults ?? collect())->count())
    <div class="space-y-3">
        @foreach($searchResults as $v)
        <a href="{{ route('quran.surah', $v->surah->number) }}#ayat-{{ $v->verse_number }}" class="block p-4 bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg">{{ $v->surah->name_latin }} : {{ $v->verse_number }}</span>
                <span class="text-xs text-gray-400">Juz {{ $v->juz_number }}</span>
            </div>
            <p class="quran-arabic-preview text-right mb-2 text-gray-900 line-clamp-1" dir="rtl">{{ $v->text_uthmani }}</p>
            <p class="text-xs text-gray-600 line-clamp-2">{{ $v->translation_id }}</p>
        </a>
        @endforeach
    </div>
    @else
    <div class="text-center py-16">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <p class="text-gray-500 font-medium">Tidak ditemukan hasil</p>
        <p class="text-gray-400 text-sm mt-1">Coba kata kunci lain</p>
    </div>
    @endif

    {{-- Tab: Surah --}}
    @elseif(($tab ?? 'surah') === 'surah')
    @if($surahs->isEmpty())
    <div class="text-center py-16 px-4">
        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-emerald-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <p class="text-gray-700 font-semibold">Memuat daftar surah...</p>
        <p class="text-gray-400 text-sm mt-1">Mohon tunggu sebentar atau muat ulang halaman</p>
        <button onclick="location.reload()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-700 text-white text-sm font-semibold rounded-xl hover:bg-emerald-800 transition-colors">
            Muat Ulang
        </button>
    </div>
    @else
    {{-- Filter Surah --}}
    <div class="mb-4" x-data="{ filter: '' }">
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            <input type="text" x-model="filter" placeholder="Filter surah..."
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
        </div>
        <div class="space-y-2 mt-3">
        @foreach($surahs as $s)
        <a href="{{ route('quran.surah', $s->number) }}"
            x-show="!filter || '{{ strtolower($s->name_latin . ' ' . $s->name_translation . ' ' . $s->number) }}'.includes(filter.toLowerCase())"
            x-transition
            class="flex items-center gap-3.5 p-3.5 bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-shadow">
            <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center shrink-0">
                <span class="text-sm font-bold text-emerald-700">{{ $s->number }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ $s->name_latin }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $s->name_translation }} &middot; {{ $s->total_verses }} ayat</p>
                    </div>
                    <div class="text-right shrink-0 ml-3">
                        <p class="quran-arabic-preview text-base text-gray-800" dir="rtl">{{ $s->name_arabic }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $s->revelation_type === 'meccan' ? 'Makkiyah' : 'Madaniyah' }}</p>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
        </div>
    </div>
    @endif

    {{-- Tab: Juz --}}
    @elseif($tab === 'juz')
    <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
        @for($j = 1; $j <= 30; $j++)
        @php
            $juz = $juzInfo->firstWhere('number', $j);
            $totalVerses = $juz ? $juz['total_verses'] : 0;
            $firstSurah = ($juz && $juz['surahs']->count()) ? $juz['surahs']->first()->name_latin : '-';
        @endphp
        <a href="{{ route('quran.juz', $j) }}" class="p-4 bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-shadow text-center">
            <div class="w-10 h-10 bg-teal-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                <span class="text-sm font-bold text-teal-700">{{ $j }}</span>
            </div>
            <h3 class="text-xs font-bold text-gray-900">Juz {{ $j }}</h3>
            <p class="text-[10px] text-gray-400 mt-1">{{ $totalVerses }} ayat</p>
            <p class="text-[10px] text-emerald-600 mt-0.5 truncate">{{ $firstSurah }}</p>
        </a>
        @endfor
    </div>

    {{-- Tab: Bookmark --}}
    @elseif($tab === 'bookmark')
    @auth
        @if($bookmarks->count())
        <div class="space-y-2">
            @foreach($bookmarks as $bm)
            <div class="flex items-center gap-3 p-3.5 bg-white rounded-2xl border border-gray-100">
                <a href="{{ route('quran.surah', $bm->surah->number) }}#ayat-{{ $bm->verse_number }}" class="flex-1 flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">{{ $bm->surah->name_latin }} : {{ $bm->verse_number }}</h3>
                        @if($bm->label)
                        <p class="text-xs text-gray-500 truncate">{{ $bm->label }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-0.5">{{ $bm->created_at->diffForHumans() }}</p>
                    </div>
                </a>
                <button onclick="removeBookmark({{ $bm->surah_id }}, {{ $bm->verse_number }}, this)" class="p-2 text-gray-400 hover:text-red-500 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16">
            <svg class="w-12 h-12 text-amber-300 mx-auto mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
            <p class="text-gray-500 font-medium">Belum ada bookmark</p>
            <p class="text-gray-400 text-sm mt-1">Pin ayat favorit saat membaca Al-Quran</p>
        </div>
        @endif
    @endauth

    {{-- Tab: Khatam --}}
    @elseif($tab === 'khatam')
    @auth
        <div class="mb-4">
            <a href="{{ route('quran.khatam.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-700 text-white text-sm font-semibold rounded-xl hover:bg-emerald-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Kelola Program Khatam
            </a>
        </div>
        @if($khatamPrograms->count())
        <div class="space-y-3">
            @foreach($khatamPrograms as $prog)
            <a href="{{ route('quran.khatam.show', $prog->id) }}" class="block p-4 bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-gray-900">{{ $prog->title }}</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $prog->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($prog->status === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                        {{ $prog->status === 'active' ? 'Aktif' : ($prog->status === 'completed' ? 'Selesai' : 'Berhenti') }}
                    </span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-2">
                    <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $prog->progress_percent }}%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500">
                    <span>{{ $prog->progress_percent }}% selesai</span>
                    <span>{{ $prog->days_remaining }} hari tersisa</span>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="text-center py-16">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
            <p class="text-gray-500 font-medium">Belum ada program khatam</p>
            <p class="text-gray-400 text-sm mt-1">Mulai program khatam Al-Quran Anda</p>
            <a href="{{ route('quran.khatam.index') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-emerald-700 text-white text-sm font-semibold rounded-xl hover:bg-emerald-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Program Khatam
            </a>
        </div>
        @endif
    @endauth
    @endif

</div>

@auth
@push('scripts')
<script>
function removeBookmark(surahId, verseNumber, btn) {
    fetch(@json(route('quran.bookmark')), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ surah_id: surahId, verse_number: verseNumber })
    }).then(r => r.json()).then(data => {
        if (!data.bookmarked) {
            const card = btn.closest('.flex.items-center');
            if (card) card.remove();
            if (typeof showToast === 'function') showToast('Bookmark dihapus', 'info');
        }
    });
}
</script>
@endpush
@endauth

@guest
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const lr = localStorage.getItem('quran_last_read');
    if (lr) {
        try {
            const d = JSON.parse(lr);
            const el = document.getElementById('last-read-guest');
            const txt = document.getElementById('last-read-guest-text');
            if (el && txt && d.surah_name && d.verse) {
                const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
                txt.textContent = d.surah_name + ' - Ayat ' + d.verse;
                el.href = baseUrl + 'quran/surah/' + d.surah_number + '#ayat-' + d.verse;
                el.style.display = 'block';
            }
        } catch(e) {}
    }
});
</script>
@endpush
@endguest
@endsection
