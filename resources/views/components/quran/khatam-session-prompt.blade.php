@props(['program'])

@if($program)
<div x-show="showKhatamSessionPrompt" x-cloak
    class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden"
        @click.outside.prevent
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
        <div class="bg-gradient-to-br from-emerald-700 to-teal-700 px-5 py-4 text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </div>
                <div>
                    <p class="text-xs text-emerald-100 font-medium">Program khatam aktif</p>
                    <p class="text-sm font-bold truncate">{{ $program->title }}</p>
                </div>
            </div>
        </div>
        <div class="px-5 py-4">
            <p class="text-sm text-gray-700 leading-relaxed mb-1">
                Apakah bacaan Anda <strong>sesi ini</strong> ingin dicatat ke progress khatam?
            </p>
            <p class="text-xs text-gray-500 mb-4">
                Pilih <em>Baca rutin</em> jika hanya ingin membaca Yasin, doa, atau tilawah tanpa menambah halaman khatam.
            </p>
            <div class="space-y-2">
                <button type="button" @click="enableKhatamSession()"
                    class="w-full py-3 px-4 bg-emerald-700 text-white text-sm font-semibold rounded-xl hover:bg-emerald-800 transition-colors">
                    Ya, catat progress khatam
                </button>
                <button type="button" @click="disableKhatamSession()"
                    class="w-full py-3 px-4 bg-gray-100 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                    Baca rutin saja (tanpa khatam)
                </button>
            </div>
            <p class="text-[10px] text-gray-400 text-center mt-3">Pilihan berlaku untuk tab browser ini. Bisa diubah kapan saja dari toolbar.</p>
        </div>
    </div>
</div>
@endif
