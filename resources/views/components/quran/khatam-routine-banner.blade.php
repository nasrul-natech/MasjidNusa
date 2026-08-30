@props(['program'])

@if($program)
<div class="bg-gray-50 border-b border-gray-200 px-4 py-2" x-show="khatamAvailable && !khatamEnabled && !showKhatamSessionPrompt" x-cloak>
    <div class="max-w-screen-md mx-auto flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 min-w-0">
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <div class="min-w-0">
                <p class="text-[10px] font-semibold text-gray-600 truncate">Mode baca rutin</p>
                <p class="text-[10px] text-gray-400 truncate">Khatam "{{ $program->title }}" tidak dicatat sesi ini</p>
            </div>
        </div>
        <button type="button" @click="enableKhatamSession()"
            class="shrink-0 text-[10px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg transition-colors">
            Aktifkan khatam
        </button>
    </div>
</div>
@endif
