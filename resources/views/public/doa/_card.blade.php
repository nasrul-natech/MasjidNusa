<div class="card mb-3 overflow-hidden">
    <a href="{{ route('doa.show', $doa->slug) }}" class="block px-5 pt-4 pb-1">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <x-badge type="info" class="text-[10px]">{{ $doa->category->name ?? '' }}</x-badge>
                    @if($doa->source)
                    <span class="text-[10px] text-gray-400">{{ $doa->source }}</span>
                    @endif
                </div>
                <h3 class="font-bold text-gray-900 text-sm">{{ $doa->title }}</h3>
            </div>
        </div>
    </a>

    @if($doa->arabic)
    <div class="px-5 py-3">
        <p class="doa-arabic text-right leading-loose text-gray-800" dir="rtl">
            {{ $doa->arabic }}
        </p>
    </div>
    @endif

    @if($doa->latin)
    <div class="px-5 pb-1">
        <p class="text-sm text-emerald-700 italic">{{ $doa->latin }}</p>
    </div>
    @endif

    <div class="px-5 pb-3">
        <p class="text-sm text-gray-600 mt-1">{{ $doa->translation }}</p>
    </div>

    <div class="flex items-center justify-between px-5 py-2.5 bg-gray-50 border-t border-gray-100">
        <a href="{{ route('doa.show', $doa->slug) }}" class="text-xs text-emerald-600 font-medium hover:underline">Baca selengkapnya</a>

        @auth
        <button onclick="toggleFav({{ $doa->id }}, this)"
            class="flex items-center gap-1 text-xs font-medium transition-colors {{ in_array($doa->id, $favoritedIds ?? []) ? 'text-amber-500' : 'text-gray-400 hover:text-amber-500' }}">
            <svg class="w-4 h-4" fill="{{ in_array($doa->id, $favoritedIds ?? []) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <span>{{ in_array($doa->id, $favoritedIds ?? []) ? 'Tersimpan' : 'Simpan' }}</span>
        </button>
        @endauth
    </div>
</div>
