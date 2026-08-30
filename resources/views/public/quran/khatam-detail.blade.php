@extends('layouts.public')

@section('title', $program->title . ' - Khatam Al-Quran')

@section('content')
<div class="max-w-screen-md mx-auto px-4 py-6" x-data="khatamTracker()">

    {{-- Header --}}
    <div class="flex items-center gap-2 mb-5">
        <a href="{{ route('quran.khatam.index') }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-bold text-gray-900 truncate">{{ $program->title }}</h1>
            <p class="text-xs text-gray-500">
                {{ $program->start_date->format('d M Y') }} - {{ $program->target_date->format('d M Y') }}
                &middot; {{ $program->target_days }} hari
            </p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium shrink-0 {{ $program->status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($program->status === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
            {{ $program->status === 'active' ? 'Aktif' : ($program->status === 'completed' ? 'Khatam' : 'Berhenti') }}
        </span>
    </div>

    {{-- Progress Card --}}
    <div class="card p-5 mb-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-gray-900">Progress</h2>
            <span class="text-lg font-bold text-emerald-700" x-text="progressPercent + '%'">{{ $program->progress_percent }}%</span>
        </div>
        <div class="h-3 bg-gray-100 rounded-full overflow-hidden mb-3">
            <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all duration-500" :style="'width:' + progressPercent + '%'"></div>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="p-2 bg-emerald-50 rounded-xl">
                <p class="text-xs text-gray-500">Halaman</p>
                <p class="text-sm font-bold text-gray-900" x-text="totalPages + '/604'">{{ $program->total_pages_read }}/604</p>
            </div>
            <div class="p-2 bg-blue-50 rounded-xl">
                <p class="text-xs text-gray-500">Hari Berlalu</p>
                <p class="text-sm font-bold text-gray-900">{{ $program->days_elapsed }}</p>
            </div>
            <div class="p-2 bg-amber-50 rounded-xl">
                <p class="text-xs text-gray-500">Sisa Hari</p>
                <p class="text-sm font-bold text-gray-900">{{ $program->days_remaining }}</p>
            </div>
        </div>

        @if($program->days_remaining > 0 && $program->total_pages_read < 604)
        <div class="mt-3 p-3 bg-gray-50 rounded-xl">
            <p class="text-xs text-gray-600">
                Target harian: <strong>{{ max(1, ceil((604 - $program->total_pages_read) / max(1, $program->days_remaining))) }} halaman/hari</strong>
                agar selesai tepat waktu.
            </p>
        </div>
        @endif
    </div>

    {{-- Record Progress --}}
    @if($program->status === 'active')
    <div class="card p-5 mb-5">
        <h2 class="text-sm font-bold text-gray-900 mb-3">Catat Bacaan Hari Ini</h2>
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Surah</label>
                    <select x-model="formSurah" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                        @foreach($surahs as $s)
                        <option value="{{ $s->number }}">{{ $s->number }}. {{ $s->name_latin }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Juz</label>
                    <select x-model="formJuz" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                        @for($i = 1; $i <= 30; $i++)
                        <option value="{{ $i }}">Juz {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Ayat Dari</label>
                    <input type="number" x-model="formFrom" min="1" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Ayat Sampai</label>
                    <input type="number" x-model="formTo" min="1" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Halaman</label>
                    <input type="number" x-model="formPages" min="1" max="20" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
            <button @click="saveProgress()" :disabled="saving"
                class="w-full bg-emerald-700 text-white font-semibold py-2.5 rounded-xl text-sm hover:bg-emerald-800 transition-colors disabled:opacity-50">
                <span x-show="!saving">Simpan Progress</span>
                <span x-show="saving" x-cloak>Menyimpan...</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Recent Progress --}}
    @if($program->progress->count())
    <h2 class="text-sm font-bold text-gray-900 mb-3">Riwayat Bacaan</h2>
    <div class="space-y-2">
        @foreach($program->progress->take(20) as $p)
        <div class="flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-100">
            <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-emerald-700">{{ $p->pages_read }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">Surah {{ $p->surah_number }} &middot; Ayat {{ $p->verse_from }}-{{ $p->verse_to }}</p>
                <p class="text-xs text-gray-500">Juz {{ $p->juz_number }} &middot; {{ $p->pages_read }} halaman</p>
            </div>
            <span class="text-xs text-gray-400 shrink-0">{{ $p->read_date->format('d M') }}</span>
        </div>
        @endforeach
    </div>
    @endif

</div>

@push('scripts')
<script>
function khatamTracker() {
    return {
        progressPercent: {{ $program->progress_percent }},
        totalPages: {{ $program->total_pages_read }},
        formSurah: '1',
        formJuz: '1',
        formFrom: 1,
        formTo: 7,
        formPages: 2,
        saving: false,

        saveProgress() {
            const from = parseInt(this.formFrom, 10);
            const to = parseInt(this.formTo, 10);
            const pages = parseInt(this.formPages, 10);

            if (to < from) {
                if (typeof showToast === 'function') showToast('Ayat sampai harus lebih besar atau sama dengan ayat dari', 'error');
                return;
            }
            if (pages < 1 || pages > 20) {
                if (typeof showToast === 'function') showToast('Jumlah halaman harus antara 1–20', 'error');
                return;
            }

            this.saving = true;
            fetch('{{ route("quran.khatam.progress", $program->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    surah_number: parseInt(this.formSurah),
                    verse_from: parseInt(this.formFrom),
                    verse_to: parseInt(this.formTo),
                    juz_number: parseInt(this.formJuz),
                    pages_read: parseInt(this.formPages),
                })
            })
            .then(r => {
                if (!r.ok) return r.json().then(err => { throw err; });
                return r.json();
            })
            .then(data => {
                this.saving = false;
                if (data.success) {
                    this.progressPercent = data.progress_percent;
                    if (data.duplicate) {
                        if (typeof showToast === 'function') showToast('Bacaan ini sudah tercatat hari ini', 'info');
                        return;
                    }
                    if (typeof data.total_pages_read === 'number') {
                        this.totalPages = data.total_pages_read;
                    } else {
                        this.totalPages += pages;
                    }
                    if (typeof showToast === 'function') showToast('Progress berhasil disimpan!', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else if (data.error) {
                    if (typeof showToast === 'function') showToast(data.error, 'error');
                }
            })
            .catch(err => {
                this.saving = false;
                const msg = err?.message || err?.errors
                    ? Object.values(err.errors || {}).flat().join(', ')
                    : 'Gagal menyimpan progress';
                if (typeof showToast === 'function') showToast(typeof msg === 'string' ? msg : 'Gagal menyimpan progress', 'error');
            });
        }
    };
}
</script>
@endpush
@endsection
