@extends('layouts.public')

@section('title', 'Program Khatam Al-Quran')

@section('content')
<div class="max-w-screen-md mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center gap-2 mb-5">
        <a href="{{ route('quran.index', ['tab' => 'khatam']) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-bold text-gray-900">Program Khatam</h1>
            <p class="text-xs text-gray-500">Atur target dan pantau progress baca Al-Quran</p>
        </div>
    </div>

    {{-- Create New Program --}}
    <div class="card p-5 mb-6" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <span class="text-sm font-bold text-gray-900">Buat Program Baru</span>
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <form x-show="open" x-transition x-cloak action="{{ route('quran.khatam.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="text-xs font-semibold text-gray-600 mb-1 block">Nama Program</label>
                <input type="text" name="title" required placeholder="Khatam Ramadhan 2026" value="{{ old('title') }}"
                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600 mb-2 block">Target Waktu</label>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                    @foreach([30 => '30 Hari', 60 => '60 Hari', 90 => '3 Bulan', 180 => '6 Bulan', 365 => '1 Tahun'] as $days => $label)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="target_days" value="{{ $days }}" class="peer sr-only" {{ $days === 30 ? 'checked' : '' }}>
                        <div class="text-center p-3 rounded-xl border-2 border-gray-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-colors">
                            <p class="text-sm font-bold text-gray-900">{{ $days }}</p>
                            <p class="text-[10px] text-gray-500">{{ $label }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="w-full bg-emerald-700 text-white font-semibold py-2.5 rounded-xl text-sm hover:bg-emerald-800 transition-colors">
                Mulai Program Khatam
            </button>
        </form>
    </div>

    {{-- Active Programs --}}
    @php $activePrograms = $programs->where('status', 'active'); @endphp
    @if($activePrograms->count())
    <h2 class="text-sm font-bold text-gray-900 mb-3">Program Aktif</h2>
    <div class="space-y-3 mb-6">
        @foreach($activePrograms as $prog)
        <a href="{{ route('quran.khatam.show', $prog->id) }}" class="block p-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-900">{{ $prog->title }}</h3>
                <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Aktif</span>
            </div>
            <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden mb-2">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all" style="width: {{ $prog->progress_percent }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
                <span>{{ $prog->progress_percent }}% &middot; {{ $prog->total_pages_read }}/604 halaman</span>
                <span>{{ $prog->days_remaining }} hari tersisa</span>
            </div>
            <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                <span>Mulai: {{ $prog->start_date->format('d M Y') }}</span>
                <span>Target: {{ $prog->target_date->format('d M Y') }}</span>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    {{-- Completed / Other Programs --}}
    @php $otherPrograms = $programs->where('status', '!=', 'active'); @endphp
    @if($otherPrograms->count())
    <h2 class="text-sm font-bold text-gray-900 mb-3">Riwayat</h2>
    <div class="space-y-2">
        @foreach($otherPrograms as $prog)
        <a href="{{ route('quran.khatam.show', $prog->id) }}" class="block p-4 bg-white rounded-2xl border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">{{ $prog->title }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $prog->start_date->format('d M Y') }} - {{ $prog->target_date->format('d M Y') }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $prog->status === 'completed' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $prog->status === 'completed' ? 'Khatam' : 'Berhenti' }}
                </span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden mt-2">
                <div class="h-full rounded-full {{ $prog->status === 'completed' ? 'bg-blue-500' : 'bg-gray-300' }}" style="width: {{ $prog->progress_percent }}%"></div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    @if($programs->isEmpty())
    <div class="text-center py-16">
        <div class="text-5xl mb-3">📖</div>
        <p class="text-gray-500 font-medium">Belum ada program khatam</p>
        <p class="text-gray-400 text-sm mt-1">Buat program pertama Anda di atas</p>
    </div>
    @endif

</div>
@endsection
