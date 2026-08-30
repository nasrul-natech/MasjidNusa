@props(['program'])

@if($program)
<div class="bg-emerald-50 border-b border-emerald-200 px-4 py-2.5" x-show="khatamEnabled">
    <div class="max-w-screen-md mx-auto">
        <div class="flex items-center gap-3">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-0.5">
                    <span class="text-[10px] font-semibold text-emerald-700 truncate">{{ $program->title }}</span>
                    <span class="text-[10px] font-bold text-emerald-700" x-text="formatKhatamPercent()">{{ $program->progress_percent }}%</span>
                </div>
                <div class="h-1.5 bg-emerald-200 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-600 rounded-full transition-all duration-500"
                        :style="'width:' + khatamProgressPercent + '%'"
                        style="width: {{ $program->progress_percent }}%"></div>
                </div>
                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-1">
                    <span class="text-[10px] font-semibold text-emerald-700" x-text="'Halaman khatam: ' + khatamPagesRead + '/604'">{{ $program->total_pages_read }}/604</span>
                    <span class="text-[10px] text-emerald-500" x-text="formatLocalReadSummary()"></span>
                </div>
            </div>
            <a href="{{ route('quran.khatam.show', $program->id) }}" class="text-[10px] text-emerald-600 font-semibold shrink-0 hover:underline">Detail</a>
        </div>
        <p class="text-[9px] text-emerald-600/80 mt-1.5 leading-relaxed pl-7">
            <strong>Mode Buku:</strong> halaman tercatat saat Lanjut / tombol halaman selesai — semua ayat otomatis tercentang.
            <strong class="ml-1">Mode Ayat:</strong> centang per ayat; halaman khatam +1 saat semua ayat di halaman tercentang.
        </p>
    </div>
</div>
@endif
