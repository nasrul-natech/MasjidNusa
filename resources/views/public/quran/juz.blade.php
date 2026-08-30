@extends('layouts.public')

@section('title', 'Juz ' . $number . ' - Al-Quran')

@php
$allVerses = $groupedVerses->flatten();
$pageGroups = $allVerses->groupBy('page_number')->sortKeys();
$mushafPages = $pageGroups->keys()->values()->toArray();
$versePlaylist = $allVerses->map(fn ($v) => [
    'surah_id' => $v->surah_id,
    'surah_number' => $v->surah->number,
    'surah_name' => $v->surah->name_latin,
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

.mushaf-frame {
    background: linear-gradient(160deg, #fefcf3 0%, #faf6ea 50%, #f5efe0 100%);
    border: 3px solid #c9a96e;
    border-radius: 6px;
    position: relative;
    max-width: 520px;
    margin: 0 auto;
    box-shadow: inset 0 0 0 1px rgba(201,169,110,0.3), 0 0 0 1px #d4b896, 0 4px 20px rgba(0,0,0,0.12);
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
.mushaf-body { flex: 1; padding: 16px 14px; }
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
    min-width: 30px; height: 30px; padding: 0 3px;
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
.mushaf-surah-divider {
    text-align: center;
    padding: 8px 14px;
    background: linear-gradient(to right, transparent, rgba(201,169,110,0.12), transparent);
}
.mushaf-surah-divider span {
    display: inline-block;
    padding: 3px 16px;
    background: linear-gradient(to right, rgba(201,169,110,0.15), rgba(201,169,110,0.25), rgba(201,169,110,0.15));
    border: 1px solid #d4b896;
    border-radius: 16px;
    font-size: 13px;
    color: #6b5430;
    font-weight: 700;
    font-family: var(--quran-font);
}
.verse-number-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 28px; height: 28px; padding: 0 6px;
    background: #ecfdf5; color: #047857; border-radius: 8px;
    font-size: 11px; font-weight: 700;
}
.verse-playing { background-color: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.3) !important; }
.verse-read { background-color: rgba(16, 185, 129, 0.04); }
.verse-read .verse-number-badge { background: #d1fae5; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
@@media (max-width: 640px) {
    .mushaf-frame { margin: 0 4px; }
    .mushaf-text { line-height: 2.4; }
    .mushaf-text span.end { min-width: 24px; height: 24px; font-size: 0.45em; }
}
</style>
@endpush

@section('content')
<div x-data="juzReader()" :class="{ 'no-tajweed': !showTajweed }">

    {{-- Header --}}
    <div class="bg-gradient-to-br from-teal-800 via-teal-700 to-emerald-700 text-white px-4 py-5">
        <div class="max-w-screen-md mx-auto">
            <div class="flex items-center gap-2">
                <a href="{{ route('quran.index', ['tab' => 'juz']) }}" class="p-1.5 rounded-lg hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="flex-1">
                    <h1 class="text-lg font-bold">Juz {{ $number }}</h1>
                    <p class="text-xs text-teal-200">{{ $allVerses->count() }} ayat &middot; {{ $groupedVerses->count() }} surah</p>
                </div>
                <div class="flex items-center gap-1">
                    @if($number > 1)
                    <a href="{{ route('quran.juz', $number - 1) }}" class="p-2 rounded-lg hover:bg-white/10 text-teal-200"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
                    @endif
                    @if($number < 30)
                    <a href="{{ route('quran.juz', $number + 1) }}" class="p-2 rounded-lg hover:bg-white/10 text-teal-200"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                    @endif
                </div>
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
            <select x-show="viewMode === 'list'" x-model="translationLang" @change="localStorage.setItem('quran_translation', translationLang)" class="bg-gray-100 border-0 rounded-lg px-2.5 py-1.5 text-xs font-medium text-gray-700 focus:ring-2 focus:ring-emerald-500 shrink-0">
                <option value="id">Indonesia</option>
                <option value="en">English</option>
                <option value="both">Keduanya</option>
            </select>
            <button @click="showTajweed = !showTajweed; localStorage.setItem('quran_tajweed', String(showTajweed))"
                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors shrink-0"
                :class="showTajweed ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'">
                <span class="w-2 h-2 rounded-full" :class="showTajweed ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                Tajweed
            </button>
            <button x-show="viewMode === 'list'" @click="showLatin = !showLatin; localStorage.setItem('quran_latin', String(showLatin))"
                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors shrink-0"
                :class="showLatin ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'">
                <span class="w-2 h-2 rounded-full" :class="showLatin ? 'bg-blue-500' : 'bg-gray-400'"></span>
                Latin
            </button>
            <button @click="showFontPanel = !showFontPanel" class="flex items-center gap-1.5 px-2.5 py-1.5 bg-gray-100 rounded-lg text-xs font-medium text-gray-700 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-text="fontSize + 'px'"></span>
            </button>
        </div>
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
                    <input type="range" :value="fontSize" @input="fontSize = Number($event.target.value); applyFont()" @change="fontSize = Number($event.target.value); applyFont()" min="20" max="56" step="2" class="flex-1 accent-emerald-600 h-2">
                </div>
            </div>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- MUSHAF VIEW                    --}}
    {{-- ============================= --}}
    <div x-show="viewMode === 'mushaf'" x-cloak class="py-4 px-2">
        @foreach($pageGroups as $pageNum => $pageVerses)
        @php
            $pageSurahs = $pageVerses->pluck('surah')->unique('id');
            $surahLabel = $pageSurahs->map(fn($s) => $s->name_latin)->implode(' / ');
        @endphp
        <template x-if="mushafPage === {{ $pageNum }}">
            <div>
                <div class="mushaf-frame">
                    <div class="mushaf-inner">
                        <div class="mushaf-header">
                            <span>Juz {{ $number }}</span>
                            <span>{{ $surahLabel }}: {{ $pageVerses->first()->verse_number }}-{{ $pageVerses->last()->verse_number }}</span>
                        </div>
                        <div class="mushaf-body">
                            @php $currentSurahId = null; @endphp
                            <p class="mushaf-text">@foreach($pageVerses as $v)@if($v->surah_id !== $currentSurahId)@php $currentSurahId = $v->surah_id; @endphp</p>
                            <div class="mushaf-surah-divider"><span>سُورَةُ {{ $v->surah->name_arabic }}</span></div>
                            <p class="mushaf-text">@endif @if($v->text_tajweed){!! $v->text_tajweed !!} @else{{ $v->text_uthmani }} <span class="end">{{ $v->verse_number }}</span> @endif @endforeach</p>
                        </div>
                        <div class="mushaf-footer">{{ $pageNum }}</div>
                    </div>
                </div>
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
    {{-- LIST VIEW                      --}}
    {{-- ============================= --}}
    <div x-show="viewMode === 'list'" class="max-w-screen-md mx-auto px-4 pb-24">
        @foreach($groupedVerses as $surahId => $verses)
        @php $surahData = $verses->first()->surah; @endphp
        <div class="py-4 text-center border-b-2 border-emerald-100 mt-4 first:mt-0">
            <a href="{{ route('quran.surah', $surahData->number) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">
                <span class="text-sm font-bold text-emerald-700">{{ $surahData->number }}.</span>
                <span class="text-sm font-bold text-gray-900">{{ $surahData->name_latin }}</span>
                <span class="text-base" style="font-family:'Amiri Quran',serif" dir="rtl">{{ $surahData->name_arabic }}</span>
            </a>
        </div>
        @foreach($verses as $verse)
        <div id="ayat-{{ $surahData->number }}-{{ $verse->verse_number }}"
            class="py-5 border-b border-gray-100 verse-item"
            :class="{ 'verse-read': isVerseRead({{ $surahData->number }}, {{ $verse->verse_number }}) }"
            data-surah="{{ $surahData->number }}" data-verse="{{ $verse->verse_number }}">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="verse-number-badge">{{ $verse->verse_number }}</span>
                    <span class="text-[10px] text-gray-400">{{ $surahData->name_latin }} · Juz {{ $verse->juz_number }} · Hal {{ $verse->page_number }}</span>
                </div>
                <x-quran.verse-toolbar
                    :surah-id="$surahData->id"
                    :surah-number="$surahData->number"
                    :verse-number="$verse->verse_number"
                    :audio-url="$verse->audio_url"
                    :bookmarked="in_array($surahData->id . ':' . $verse->verse_number, $bookmarkedVerses)"
                />
            </div>
            <div class="mb-3 text-right" dir="rtl">
                @if($verse->text_tajweed)
                <p class="quran-text">{!! $verse->text_tajweed !!}</p>
                @else
                <p class="quran-text">{{ $verse->text_uthmani }}</p>
                @endif
            </div>
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
        @endforeach
    </div>

    {{-- Navigation --}}
    <div class="max-w-screen-md mx-auto px-4 py-4 flex justify-between border-t border-gray-100 mb-20">
        @if($number > 1)
        <a href="{{ route('quran.juz', $number - 1) }}" class="flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Juz {{ $number - 1 }}
        </a>
        @else <div></div> @endif
        @if($number < 30)
        <a href="{{ route('quran.juz', $number + 1) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-700 rounded-xl text-sm font-medium text-white hover:bg-emerald-800 transition-colors">
            Juz {{ $number + 1 }}
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
                    <p class="text-xs text-gray-400 truncate" x-text="currentAyatLabel"></p>
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
</div>

@push('scripts')
<script>
function juzReader() {
    const mushafPagesArr = @json($mushafPages);

    const core = quranReaderCore({
        versePlaylist: @json($versePlaylist),
        bookmarkedKeys: @json($bookmarkedVerses),
        pageMetaMap: @json($pageMetaMap->all()),
        khatamProgramId: @json($activeKhatam?->id),
        khatamProgramTitle: @json($activeKhatam?->title),
        khatamProgressUrl: @json($activeKhatam ? route('quran.khatam.progress', $activeKhatam->id) : null),
        initialKhatamProgress: @json($activeKhatam?->progress_percent ?? 0),
        initialKhatamPagesRead: @json($activeKhatam?->total_pages_read ?? 0),
        defaultSurahNumber: null,
        defaultSurahId: null,
        defaultSurahName: 'Juz {{ $number }}',
        juzNumber: {{ $number }},
    });

    return {
        ...core,
        viewMode: localStorage.getItem('quran_view') || 'list',
        showTajweed: localStorage.getItem('quran_tajweed') !== 'false',
        showLatin: localStorage.getItem('quran_latin') === 'true',
        translationLang: localStorage.getItem('quran_translation') || 'id',
        showFontPanel: false,
        fontFamily: localStorage.getItem('quran_font') || "'Amiri Quran', serif",
        fontSize: parseInt(localStorage.getItem('quran_font_size') || '28'),
        mushafPages: mushafPagesArr,
        mushafPageIdx: 0,
        mushafPage: mushafPagesArr[0] || 1,

        init() {
            this.initKhatamSession();
            this.initReadVerses();
            this.applyFont();
            this.$watch('translationLang', v => localStorage.setItem('quran_translation', v));
            this.$watch('showTajweed', v => localStorage.setItem('quran_tajweed', String(v)));
            this.$watch('showLatin', v => localStorage.setItem('quran_latin', String(v)));
            this.initReaderScroll();
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
    };
}
</script>
@endpush
@endsection
