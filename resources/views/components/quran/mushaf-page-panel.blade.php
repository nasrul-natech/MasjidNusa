@props(['pageVerses', 'bookmarkedVerses' => []])

<div class="max-w-[520px] mx-auto mt-2 px-2" x-data="{ panelOpen: false }">
    <button type="button" @click="panelOpen = !panelOpen"
        class="w-full flex items-center justify-between gap-2 px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 transition-colors shadow-sm">
        <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            Ayat di halaman ini ({{ $pageVerses->count() }})
        </span>
        <svg class="w-4 h-4 transition-transform" :class="panelOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div x-show="panelOpen" x-cloak class="mt-2 space-y-1.5">
        @foreach($pageVerses as $v)
        @php
            $sn = $v->surah->number;
            $vn = $v->verse_number;
            $bk = is_array($bookmarkedVerses)
                ? (isset($bookmarkedVerses[0]) && is_string($bookmarkedVerses[0])
                    ? in_array($v->surah_id . ':' . $vn, $bookmarkedVerses)
                    : in_array($vn, $bookmarkedVerses))
                : false;
        @endphp
        <div id="ayat-{{ $sn }}-{{ $vn }}"
            class="flex items-center justify-between gap-2 py-2 px-3 bg-white rounded-xl border border-gray-100 verse-item"
            :class="{ 'verse-read': isVerseRead({{ $sn }}, {{ $vn }}) }"
            data-surah="{{ $sn }}" data-verse="{{ $vn }}">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-800 truncate">{{ $v->surah->name_latin }} : {{ $vn }}</p>
                <p class="text-[10px] text-gray-400">Juz {{ $v->juz_number }}</p>
            </div>
            <x-quran.verse-toolbar
                :surah-id="$v->surah_id"
                :surah-number="$sn"
                :verse-number="$vn"
                :audio-url="$v->audio_url"
                :bookmarked="$bk"
            />
        </div>
        @endforeach
    </div>
</div>
