@props([
    'surahId',
    'surahNumber',
    'verseNumber',
    'audioUrl',
    'bookmarked' => false,
])

<div class="flex items-center gap-0.5 shrink-0">
    <button type="button"
        @click="toggleVerseRead({{ $surahNumber }}, {{ $verseNumber }})"
        class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
        :class="isVerseRead({{ $surahNumber }}, {{ $verseNumber }}) ? 'text-emerald-600' : 'text-gray-300'"
        :title="isVerseRead({{ $surahNumber }}, {{ $verseNumber }}) ? 'Ayat sudah dibaca' : 'Tandai ayat dibaca'">
        <svg class="w-4 h-4" :fill="isVerseRead({{ $surahNumber }}, {{ $verseNumber }}) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </button>
    <button type="button"
        @click="playAudio({{ $surahNumber }}, {{ $verseNumber }}, @js($audioUrl))"
        class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
        :class="isAyatPlaying({{ $surahNumber }}, {{ $verseNumber }}) ? 'text-emerald-600 bg-emerald-50' : 'text-gray-400'"
        title="Putar audio">
        <svg x-show="!isAyatPlaying({{ $surahNumber }}, {{ $verseNumber }})" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        <svg x-show="isAyatPlaying({{ $surahNumber }}, {{ $verseNumber }})" x-cloak class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
    </button>
    <button type="button"
        @click="toggleBookmark({{ $surahId }}, {{ $verseNumber }}, $el)"
        class="p-2 rounded-lg hover:bg-gray-100 transition-colors bookmark-btn-{{ $surahNumber }}-{{ $verseNumber }} {{ $bookmarked ? 'text-amber-500' : 'text-gray-400' }}"
        title="Pin ayat">
        <svg class="w-4 h-4" fill="{{ $bookmarked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
    </button>
</div>
