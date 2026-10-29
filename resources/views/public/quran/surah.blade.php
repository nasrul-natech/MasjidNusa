@extends('layouts.public')

@section('title', $surah->name_latin . ' - Al-Quran')
@section('meta_description', 'Baca Surah ' . $surah->name_latin . ' dengan tajweed dan terjemahan')

@php
$pageGroups = $verses->groupBy('page_number')->sortKeys();
$mushafPages = $pageGroups->keys()->values()->toArray();
$versePlaylist = $verses->map(fn ($v) => [
    'surah_id' => $surah->id,
    'surah_number' => $surah->number,
    'surah_name' => $surah->name_latin,
    'verse_number' => $v->verse_number,
    'page_number' => $v->page_number,
    'juz_number' => $v->juz_number,
    'audio_url' => $v->audio_url,
])->values();
@endphp

<x-quran.reader-core />

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Amiri+Quran&family=Scheherazade+New:wght@400;700&family=Noto+Naskh+Arabic:wght@400;700&display=swap" rel="stylesheet">
<style>
:root {
    --quran-font: 'Amiri Quran', serif;
    --quran-size: 28px;
}
.quran-text {
    font-family: var(--quran-font);
    font-size: var(--quran-size);
    line-height: 2.2;
    direction: rtl;
    word-spacing: 8px;
    color: #1a1a1a;
}
.bismillah-text {
    font-family: var(--quran-font);
    font-size: calc(var(--quran-size) + 4px);
    line-height: 2;
    color: #1a1a1a;
}

/* === Tajweed colors (matches API <tajweed class=xxx>) === */
tajweed { color: inherit; }
tajweed.ham_wasl { color: #AAAAAA; }
tajweed.slnt { color: #AAAAAA; }
tajweed.laam_shamsiyah { color: #FF7E1E; }
tajweed.madda_normal { color: #537FFF; }
tajweed.madda_permissible { color: #4050FF; }
tajweed.madda_obligatory { color: #000EBC; }
tajweed.qalaqah { color: #DD0008; }
tajweed.ikhpiaa { color: #9400A8; }
tajweed.ghunnah { color: #FF7E1E; }
tajweed.idghaam_no_ghunnah { color: #169200; }
tajweed.idghaam_ghunnah { color: #169200; }
tajweed.iqlab { color: #26BFFD; }
tajweed.ikhpiaa_shafawi { color: #D500B7; }
.no-tajweed tajweed { color: inherit !important; }

/* === Mushaf Frame === */
.mushaf-frame {
    background: linear-gradient(160deg, #fefcf3 0%, #faf6ea 50%, #f5efe0 100%);
    border: 3px solid #c9a96e;
    border-radius: 6px;
    position: relative;
    max-width: 520px;
    margin: 0 auto;
    box-shadow:
        inset 0 0 0 1px rgba(201,169,110,0.3),
        0 0 0 1px #d4b896,
        0 4px 20px rgba(0,0,0,0.12);
}
.mushaf-inner {
    border: 1px solid #d4b896;
    margin: 5px;
    min-height: 55vh;
    display: flex;
    flex-direction: column;
}
.mushaf-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 14px;
    background: linear-gradient(to right, rgba(201,169,110,0.08), rgba(201,169,110,0.15), rgba(201,169,110,0.08));
    border-bottom: 1px solid #d4b896;
    font-size: 11px;
    color: #8b7355;
    font-weight: 600;
}
.mushaf-body {
    flex: 1;
    padding: 16px 14px;
}
.mushaf-text {
    font-family: var(--quran-font);
    font-size: var(--quran-size);
    line-height: 2.6;
    word-spacing: 4px;
    color: #1a1a1a;
    text-align: justify;
    direction: rtl;
}
.mushaf-text span.end {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 3px;
    border: 1.5px solid #c9a96e;
    border-radius: 50%;
    font-size: 0.5em;
    color: #8b6d3f;
    margin: 0 3px;
    vertical-align: middle;
    background: rgba(201,169,110,0.06);
    line-height: 1;
    font-weight: 700;
}
.mushaf-footer {
    text-align: center;
    padding: 6px 14px;
    border-top: 1px solid #d4b896;
    background: linear-gradient(to right, rgba(201,169,110,0.08), rgba(201,169,110,0.15), rgba(201,169,110,0.08));
    font-size: 13px;
    color: #8b7355;
    font-weight: 700;
}
.mushaf-surah-banner {
    text-align: center;
    padding: 10px 14px 6px;
    background: linear-gradient(to right, transparent, rgba(201,169,110,0.12), transparent);
}
.mushaf-surah-banner span {
    display: inline-block;
    padding: 4px 20px;
    background: linear-gradient(to right, rgba(201,169,110,0.15), rgba(201,169,110,0.25), rgba(201,169,110,0.15));
    border: 1px solid #d4b896;
    border-radius: 20px;
    font-size: 15px;
    color: #6b5430;
    font-weight: 700;
    font-family: var(--quran-font);
}
.mushaf-bismillah {
    text-align: center;
    padding: 4px 14px 8px;
    font-family: var(--quran-font);
    font-size: calc(var(--quran-size) - 2px);
    color: #1a1a1a;
    line-height: 2;
}

/* === List view helpers === */
.verse-number-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 28px; height: 28px; padding: 0 6px;
    background: var(--color-emerald-50); color: var(--color-primary); border-radius: 8px;
    font-size: 11px; font-weight: 700;
}
.verse-playing { background-color: color-mix(in srgb, var(--color-primary) 8%, transparent); border-color: color-mix(in srgb, var(--color-primary) 30%, transparent) !important; }
.verse-read { background-color: color-mix(in srgb, var(--color-primary) 4%, transparent); }
.verse-read .verse-number-badge { background: var(--color-emerald-100); }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

/* === Khatam progress === */
.khatam-bar-bg { background: linear-gradient(to right, var(--color-emerald-100), var(--color-emerald-200)); }

@@media (max-width: 640px) {
    .mushaf-frame { margin: 0 4px; }
    .mushaf-text { line-height: 2.4; }
    .mushaf-text span.end { min-width: 24px; height: 24px; font-size: 0.45em; }
}
</style>
@endpush

@section('content')
<div x-data="quranReader()" :class="{ 'no-tajweed': !showTajweed }">

    {{-- Surah Header --}}
    <div class="bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-700 text-white px-4 py-5">
        <div class="max-w-screen-md mx-auto">
            <div class="flex items-center gap-2 mb-3">
                <a href="{{ route('quran.index') }}" class="p-1.5 rounded-lg hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="flex-1 min-w-0">
                    <h1 class="text-lg font-bold truncate">{{ $surah->name_latin }}</h1>
                    <p class="text-xs text-emerald-200">{{ $surah->name_translation }} &middot; {{ $surah->total_verses }} ayat &middot; {{ $surah->revelation_type === 'meccan' ? 'Makkiyah' : 'Madaniyah' }}</p>
                </div>
                <p class="text-2xl font-bold shrink-0" style="font-family:'Amiri Quran',serif" dir="rtl">{{ $surah->name_arabic }}</p>
            </div>
            <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
                <select x-model="selectedSurah" @change="goToSurah()" class="bg-white/10 border border-white/20 rounded-lg px-2 py-1 text-xs text-white focus:outline-none min-w-0">
                    @foreach($surahs as $s)
                    <option value="{{ $s->number }}" {{ $s->number === $surah->number ? 'selected' : '' }} class="text-gray-900">{{ $s->number }}. {{ $s->name_latin }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Khatam session --}}
    <x-quran.khatam-session-prompt :program="$activeKhatam" />
    <x-quran.khatam-routine-banner :program="$activeKhatam" />
    <x-quran.khatam-progress-bar :program="$activeKhatam" />

    {{-- Toolbar --}}
    <div class="sticky top-14 z-30 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-screen-md mx-auto px-4 py-2 flex items-center gap-2 overflow-x-auto scrollbar-hide">
            <button x-show="khatamAvailable" x-cloak @click="toggleKhatamSession()"
                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors shrink-0"
                :class="khatamEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                :title="khatamEnabled ? 'Progress khatam dicatat sesi ini' : 'Mode baca rutin, khatam tidak dicatat'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span x-text="khatamEnabled ? 'Khatam' : 'Rutin'"></span>
            </button>

            <button @click="viewMode = viewMode === 'list' ? 'mushaf' : 'list'; localStorage.setItem('quran_view', viewMode)"
                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors shrink-0"
                :class="viewMode === 'mushaf' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500'">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H6zm0 2h12v16H6V4z"/></svg>
                <span x-text="viewMode === 'mushaf' ? 'Buku' : 'List'"></span>
            </button>

            <select x-show="viewMode === 'list'" x-model="translationLang" class="bg-gray-100 border-0 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-emerald-500 shrink-0">
                <option value="id">Indonesia</option>
                <option value="en">English</option>
                <option value="both">Keduanya</option>
            </select>

            <button @click="showTajweed = !showTajweed" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors shrink-0"
                :class="showTajweed ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'">
                <span class="w-2 h-2 rounded-full" :class="showTajweed ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                Tajweed
            </button>

            <button x-show="viewMode === 'list'" @click="showLatin = !showLatin" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors shrink-0"
                :class="showLatin ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                <span class="w-2 h-2 rounded-full" :class="showLatin ? 'bg-blue-500' : 'bg-gray-400'"></span>
                Latin
            </button>

            <button @click="showFontPanel = !showFontPanel" class="flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 rounded-lg text-xs font-medium text-gray-700 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-text="fontSize + 'px'"></span>
            </button>
        </div>

        {{-- Font Settings Panel --}}
        <div x-show="showFontPanel" x-transition x-cloak class="border-t border-gray-100 bg-gray-50 px-4 py-3">
            <div class="max-w-screen-md mx-auto space-y-3">
                <div class="flex items-center gap-3">
                    <label class="text-xs font-medium text-gray-600 w-12 shrink-0">Font</label>
                    <select x-model="fontFamily" @change="applyFont()" class="flex-1 bg-white border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-emerald-500">
                        <option value="'Amiri Quran', serif">Amiri Quran</option>
                        <option value="'Scheherazade New', serif">Scheherazade New</option>
                        <option value="'Noto Naskh Arabic', serif">Noto Naskh Arabic</option>
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <label class="text-xs font-medium text-gray-600 w-12 shrink-0">Size</label>
                    <button @click="fontSize = Math.max(20, fontSize - 2); applyFont()" class="w-9 h-9 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-100 text-base font-bold shrink-0 active:bg-gray-200">−</button>
                    <span class="text-sm font-bold text-gray-800 w-12 text-center" x-text="fontSize + 'px'"></span>
                    <button @click="fontSize = Math.min(56, fontSize + 2); applyFont()" class="w-9 h-9 bg-white border border-gray-200 rounded-lg flex items-center justify-center text-gray-600 hover:bg-gray-100 text-base font-bold shrink-0 active:bg-gray-200">+</button>
                    <input type="range" :value="fontSize"
                        @input="fontSize = Number($event.target.value); applyFont()"
                        @change="fontSize = Number($event.target.value); applyFont()"
                        min="20" max="56" step="2" class="flex-1 accent-emerald-600 h-2">
                </div>
                <div class="p-3 bg-white rounded-xl border border-gray-200 text-center" dir="rtl">
                    <p class="quran-text">بِسْمِ ٱللَّهِ ٱلرَّحْمَـٰنِ ٱلرَّحِيمِ</p>
                    <p class="text-[10px] text-gray-400 mt-1" dir="ltr">Preview ukuran font</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- VIEW MODE: MUSHAF (Book)      --}}
    {{-- ============================= --}}
    <div x-show="viewMode === 'mushaf'" x-cloak class="py-4 px-2">
        @foreach($pageGroups as $pageNum => $pageVerses)
        <template x-if="mushafPage === {{ $pageNum }}">
            <div>
                <div class="mushaf-frame">
                    <div class="mushaf-inner">
                        <div class="mushaf-header">
                            <span>Juz {{ $pageVerses->first()->juz_number }}</span>
                            <span>{{ $surah->number }}. {{ $surah->name_latin }}: {{ $pageVerses->first()->verse_number }}-{{ $pageVerses->last()->verse_number }}</span>
                        </div>

                        @if($pageNum == $mushafPages[0] && $surah->number !== 1 && $surah->number !== 9)
                        <div class="mushaf-surah-banner">
                            <span>سُورَةُ {{ $surah->name_arabic }}</span>
                        </div>
                        <div class="mushaf-bismillah" dir="rtl">بِسْمِ ٱللَّهِ ٱلرَّحْمَـٰنِ ٱلرَّحِيمِ</div>
                        @endif

                        <div class="mushaf-body">
                            <p class="mushaf-text">@foreach($pageVerses as $v)@if($v->text_tajweed){!! $v->text_tajweed !!} @else{{ $v->text_uthmani }} <span class="end">{{ $v->verse_number }}</span> @endif @endforeach</p>
                        </div>

                        <div class="mushaf-footer">{{ $pageNum }}</div>
                    </div>
                </div>

                {{-- Page Actions --}}
                <div class="max-w-[520px] mx-auto mt-3 flex items-center justify-between gap-2 px-2">
                    <button @click="markPageRead({{ $pageNum }})"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors"
                        :class="isPageRead({{ $pageNum }}) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-emerald-50'"
                        :title="isPageRead({{ $pageNum }}) ? 'Batalkan halaman selesai' : 'Tandai halaman ini selesai dibaca'">
                        <svg class="w-3.5 h-3.5" :fill="isPageRead({{ $pageNum }}) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="isPageRead({{ $pageNum }}) ? 'Halaman selesai' : 'Selesai baca halaman'"></span>
                    </button>
                    <span class="text-[10px] text-gray-400">Halaman {{ $pageNum }} dari 604</span>
                </div>

                <x-quran.mushaf-page-panel :page-verses="$pageVerses" :bookmarked-verses="$bookmarkedVerses" />
            </div>
        </template>
        @endforeach

        {{-- Page Navigation --}}
        <div class="max-w-[520px] mx-auto mt-4 mb-8 flex items-center justify-between gap-2 px-2">
            <button @click="prevMushafPage()" :disabled="mushafPageIdx <= 0"
                class="flex items-center gap-1.5 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-30 disabled:pointer-events-none shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Sebelum
            </button>
            <div class="text-center">
                <span class="text-xs font-bold text-gray-700" x-text="'Halaman ' + mushafPage"></span>
                <span class="text-[10px] text-gray-400 block" x-text="(mushafPageIdx + 1) + ' / ' + mushafPages.length + ' hlm'"></span>
            </div>
            <button @click="nextMushafPage()" :disabled="mushafPageIdx >= mushafPages.length - 1"
                class="flex items-center gap-1.5 px-4 py-2.5 bg-emerald-700 border border-emerald-700 rounded-xl text-sm font-medium text-white hover:bg-emerald-800 transition-colors disabled:opacity-30 disabled:pointer-events-none shadow-sm">
                Lanjut
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- VIEW MODE: LIST               --}}
    {{-- ============================= --}}
    <div x-show="viewMode === 'list'">
        {{-- Bismillah --}}
        @if($surah->number !== 1 && $surah->number !== 9)
        <div class="max-w-screen-md mx-auto px-4 py-6 text-center">
            <p class="bismillah-text" dir="rtl">بِسْمِ ٱللَّهِ ٱلرَّحْمَـٰنِ ٱلرَّحِيمِ</p>
            <p class="text-xs text-gray-500 mt-1">Dengan nama Allah Yang Maha Pengasih, Maha Penyayang</p>
        </div>
        @endif

        <div class="max-w-screen-md mx-auto px-4 pb-32">
            @foreach($verses as $verse)
            <div id="ayat-{{ $verse->verse_number }}"
                class="py-5 border-b border-gray-100 verse-item"
                :class="{ 'verse-read': isVerseRead({{ $surah->number }}, {{ $verse->verse_number }}) }"
                data-surah="{{ $surah->number }}" data-verse="{{ $verse->verse_number }}">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="verse-number-badge">{{ $verse->verse_number }}</span>
                        <span class="text-[10px] text-gray-400">Juz {{ $verse->juz_number }} · Hal {{ $verse->page_number }}</span>
                    </div>
                    <x-quran.verse-toolbar
                        :surah-id="$surah->id"
                        :surah-number="$surah->number"
                        :verse-number="$verse->verse_number"
                        :audio-url="$verse->audio_url"
                        :bookmarked="in_array($verse->verse_number, $bookmarkedVerses)"
                    />
                </div>

                <div class="mb-3 text-right" dir="rtl">
                    @if($verse->text_tajweed)
                    <p class="quran-text">{!! $verse->text_tajweed !!}</p>
                    @else
                    <p class="quran-text">{{ $verse->text_uthmani }}</p>
                    @endif
                </div>

                <div class="hidden" id="verse-data-{{ $verse->verse_number }}"
                    data-arabic="{{ $verse->text_uthmani }}"
                    data-translation-id="{{ $verse->translation_id }}"></div>

                <div x-show="showLatin" x-transition x-cloak class="mb-2">
                    @if($verse->text_transliteration)
                    <p class="text-sm text-emerald-700 italic leading-relaxed">{{ $verse->text_transliteration }}</p>
                    @endif
                </div>

                <div>
                    <p x-show="translationLang === 'id' || translationLang === 'both'" class="text-sm text-gray-700 leading-relaxed">{{ $verse->translation_id }}</p>
                    @if($verse->translation_en)
                    <p x-show="translationLang === 'en' || translationLang === 'both'" x-cloak class="text-sm text-gray-600 leading-relaxed mt-1.5 italic">{{ $verse->translation_en }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Navigation Footer --}}
    <div class="max-w-screen-md mx-auto px-4 py-4 flex items-center justify-between border-t border-gray-100 mb-20">
        @if($prevSurah)
        <a href="{{ route('quran.surah', $prevSurah->number) }}" class="flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ $prevSurah->name_latin }}
        </a>
        @else <div></div> @endif
        @if($nextSurah)
        <a href="{{ route('quran.surah', $nextSurah->number) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-700 rounded-xl text-sm font-medium text-white hover:bg-emerald-800 transition-colors">
            {{ $nextSurah->name_latin }}
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        @else <div></div> @endif
    </div>

    {{-- Audio Mini Player --}}
    <div x-show="currentAyat > 0" x-transition x-cloak class="fixed bottom-16 md:bottom-4 left-0 right-0 z-30 px-4">
        <div class="max-w-screen-md mx-auto bg-gray-900 text-white rounded-2xl shadow-2xl p-3">
            <div class="flex items-center gap-3">
                <button @click="togglePlay()" class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shrink-0 hover:bg-emerald-500 transition-colors">
                    <svg x-show="isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                    <svg x-show="!isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </button>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-400 truncate" x-text="currentAyatLabel || '{{ $surah->name_latin }}'"></p>
                    <p class="text-sm font-semibold">Ayat <span x-text="currentAyat"></span></p>
                </div>
                <div class="flex items-center gap-1">
                    <button @click="playPrevInPlaylist()" class="p-2 text-gray-400 hover:text-white"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>
                    <button @click="playNextInPlaylistManual()" class="p-2 text-gray-400 hover:text-white"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>
                    <button @click="stopAudio()" class="p-2 text-gray-400 hover:text-red-400"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 6h12v12H6z"/></svg></button>
                </div>
            </div>
            <div class="mt-2 h-1 bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all" :style="'width:' + audioProgress + '%'"></div>
            </div>
        </div>
    </div>

    {{-- Back to Top --}}
    <button x-show="showBackTop" x-transition @click="window.scrollTo({top:0,behavior:'smooth'})"
        class="fixed bottom-36 md:bottom-20 right-4 w-10 h-10 bg-white shadow-lg border border-gray-200 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-50 z-20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
    </button>
</div>

@push('scripts')
<script>
function quranReader() {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/';
    const mushafPagesArr = @json($mushafPages);

    const core = quranReaderCore({
        versePlaylist: @json($versePlaylist),
        bookmarkedKeys: @json(collect($bookmarkedVerses)->map(fn ($vn) => $surah->id . ':' . $vn)->values()),
        pageMetaMap: @json($pageMetaMap->all()),
        khatamProgramId: @json($activeKhatam?->id),
        khatamProgramTitle: @json($activeKhatam?->title),
        khatamProgressUrl: @json($activeKhatam ? route('quran.khatam.progress', $activeKhatam->id) : null),
        initialKhatamProgress: @json($activeKhatam?->progress_percent ?? 0),
        initialKhatamPagesRead: @json($activeKhatam?->total_pages_read ?? 0),
        defaultSurahNumber: {{ $surah->number }},
        defaultSurahId: {{ $surah->id }},
        defaultSurahName: @json($surah->name_latin),
        juzNumber: null,
    });

    return {
        ...core,
        selectedSurah: {{ $surah->number }},
        viewMode: localStorage.getItem('quran_view') || 'list',
        translationLang: localStorage.getItem('quran_translation') || 'id',
        showTajweed: localStorage.getItem('quran_tajweed') !== 'false',
        showLatin: localStorage.getItem('quran_latin') === 'true',
        showFontPanel: false,
        fontFamily: localStorage.getItem('quran_font') || "'Amiri Quran', serif",
        fontSize: parseInt(localStorage.getItem('quran_font_size') || '28'),
        mushafPages: mushafPagesArr,
        mushafPageIdx: 0,
        mushafPage: mushafPagesArr[0] || 1,
        showBackTop: false,

        init() {
            this.initKhatamSession();
            this.initReadVerses();
            this.applyFont();
            this.$watch('translationLang', v => localStorage.setItem('quran_translation', v));
            this.$watch('showTajweed', v => localStorage.setItem('quran_tajweed', String(v)));
            this.$watch('showLatin', v => localStorage.setItem('quran_latin', String(v)));
            this.initReaderScroll();
            window.addEventListener('scroll', () => { this.showBackTop = window.scrollY > 400; });

            const hash = window.location.hash;
            if (hash && hash.startsWith('#ayat-')) {
                const el = document.querySelector(hash);
                if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'center' }), 300);
            }
        },

        applyFont() {
            let s = document.getElementById('quran-font-override');
            if (!s) { s = document.createElement('style'); s.id = 'quran-font-override'; document.head.appendChild(s); }
            s.textContent = '.quran-text,.mushaf-text,.bismillah-text,.mushaf-bismillah{font-family:' + this.fontFamily + ' !important;font-size:' + this.fontSize + 'px !important;}';
            document.documentElement.style.setProperty('--quran-font', this.fontFamily);
            document.documentElement.style.setProperty('--quran-size', this.fontSize + 'px');
            localStorage.setItem('quran_font', this.fontFamily);
            localStorage.setItem('quran_font_size', String(this.fontSize));
        },

        goToSurah() {
            window.location.href = baseUrl + 'quran/surah/' + this.selectedSurah;
        },

        nextMushafPage() {
            if (this.khatamEnabled && !this.isPageRead(this.mushafPage)) {
                this.markPageRead(this.mushafPage);
            }
            if (this.mushafPageIdx < this.mushafPages.length - 1) {
                this.mushafPageIdx++;
                this.mushafPage = this.mushafPages[this.mushafPageIdx];
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        prevMushafPage() {
            if (this.mushafPageIdx > 0) {
                this.mushafPageIdx--;
                this.mushafPage = this.mushafPages[this.mushafPageIdx];
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        copyVerse(num) {
            const d = document.getElementById('verse-data-' + num);
            if (!d) return;
            const text = d.dataset.arabic + '\n\n' + d.dataset.translationId + '\n\n(QS. {{ $surah->name_latin }}: ' + num + ')';
            navigator.clipboard.writeText(text).then(() => {
                if (typeof showToast === 'function') showToast('Ayat berhasil disalin', 'success');
            });
        },
    };
}
</script>
@endpush
@endsection
