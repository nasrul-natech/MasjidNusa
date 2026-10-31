@extends('layouts.public')
@section('title', 'Peringkat Kehadiran')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 sm:py-8 user-page-shell">
    <a href="{{ route('user.attendance') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-700 hover:text-emerald-900">← Kehadiran Saya</a>
    <div class="rounded-2xl overflow-hidden shadow-lg mt-4 mb-4 sm:mb-6 bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 text-white p-4 sm:p-6">
        <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-white/75">Komunitas</p>
        <h1 class="text-xl sm:text-3xl font-bold mt-0.5">Peringkat Kehadiran</h1>
        <p class="mt-1.5 text-xs sm:text-sm text-white/90 line-clamp-2 sm:line-clamp-none">Semangat hadir bersama. Hanya jamaah aktif tanpa peran admin yang masuk peringkat.</p>
        <p class="mt-1 hidden sm:block text-xs text-white/75">Nama dan poin terlihat sesama jamaah yang login. Tidak ditampilkan di TV publik.</p>
    </div>
    <form method="GET" action="{{ route('user.attendance.leaderboard') }}" class="card overflow-hidden mb-4 sm:mb-5 shadow-sm user-compact-filter">
        <div class="card-header bg-indigo-50 border-b border-indigo-100 flex items-center justify-between gap-2 py-2 sm:py-3 px-3 sm:px-5">
            <label for="period" class="text-xs sm:text-sm font-semibold text-indigo-950 shrink-0">Rentang</label>
            <x-badge type="info" class="!text-[10px] sm:!text-xs !px-2 !py-0">Filter</x-badge>
        </div>
        <div class="card-body !p-3 sm:!p-5">
        <div class="flex flex-row items-center gap-2">
            <select id="period" name="period" class="form-input flex-1 min-w-0 !py-2 !text-xs sm:!text-sm">
                <option value="all" @selected(!$period)>Sepanjang waktu</option>
                @foreach($periods as $choice)
                    <option value="{{ $choice->id }}" @selected($period?->id === $choice->id)>{{ $choice->name }}{{ $choice->status === 'final' ? ' (ditutup)' : '' }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary shadow-sm shrink-0 !px-3 sm:!px-4 !py-2 text-xs sm:text-sm">OK</button>
        </div>
        <p class="mt-2 hidden sm:block text-sm leading-relaxed text-gray-600">Peringkat motivasi, bukan daftar penerima hadiah. @if($period?->status === 'final')Poin dibatasi waktu penutupan periode.@else Poin mengikuti kehadiran pada rentang terpilih.@endif</p>
        </div>
    </form>
    <section aria-label="Peringkat Anda" class="mb-4 sm:mb-6">
        <x-stat-card variant="filled" label="Peringkat Anda" :value="$ownRank ? '#' . $ownRank . ' / ' . $leaders->total() : 'Belum berperingkat'" iconName="calendar" color="amber" class="!p-4 sm:!p-5" />
        <p class="mt-2 text-xs sm:text-sm font-medium text-gray-700">{{ $own ? $own->points : 0 }} poin · rentang ini @if(!$own)— check-in saat absensi dibuka @endif</p>
    </section>
    <div class="flex items-center justify-between gap-2 mb-2">
        <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wide text-violet-800 truncate min-w-0">{{ $period?->name ?? 'Sepanjang waktu' }}</h2>
        <x-badge type="purple" class="shrink-0 !text-[10px] sm:!text-xs">{{ $leaders->total() }}</x-badge>
    </div>
    <p class="mb-3 hidden sm:block text-sm text-gray-600">Poin terbanyak lebih dahulu; jika sama, waktu mencapai poin lebih awal.</p>
    <ol start="{{ $leaders->firstItem() ?? 1 }}" class="space-y-2 sm:space-y-3" aria-label="Daftar peringkat kehadiran">
        @forelse($leaders as $leader)
            @php($rank = $leaders->firstItem() + $loop->index)
            @php($isOwn = $leader->user_id === auth()->id())
            @php($rankAccent = match(true) {
                $rank === 1 => 'border-l-amber-400 bg-gradient-to-r from-amber-50 to-white',
                $rank === 2 => 'border-l-slate-400 bg-gradient-to-r from-slate-50 to-white',
                $rank === 3 => 'border-l-orange-400 bg-gradient-to-r from-orange-50 to-white',
                default => 'border-l-indigo-200 bg-white',
            })
            <li class="card overflow-hidden p-0 flex items-center border-l-4 shadow-sm min-h-[2.75rem] {{ $rankAccent }} {{ $isOwn ? 'ring-1 sm:ring-2 ring-emerald-500 ring-offset-1 sm:ring-offset-2' : '' }}">
                <span class="shrink-0 w-9 sm:w-14 flex items-center justify-center text-xs sm:text-lg font-black tabular-nums {{ $rank === 1 ? 'text-amber-600 bg-amber-100/80' : ($rank === 2 ? 'text-slate-600 bg-slate-100/80' : ($rank === 3 ? 'text-orange-700 bg-orange-100/80' : 'text-indigo-700 bg-indigo-50')) }}">#{{ $rank }}</span>
                <div class="flex-1 min-w-0 flex items-center gap-2 pr-2 sm:pr-3 py-2 sm:py-3 pl-1 sm:pl-0">
                    <span class="min-w-0 flex-1 truncate font-semibold text-gray-900 text-xs sm:text-sm" title="{{ $leader->display_name }}">{{ $leader->display_name }}@if($isOwn)<span class="text-emerald-700 font-bold"> · Anda</span>@endif</span>
                    <span class="shrink-0 rounded-md bg-emerald-600 px-2 py-0.5 sm:px-3 sm:py-1 text-[10px] sm:text-sm font-bold text-white tabular-nums leading-tight">{{ $leader->points }}<span class="hidden sm:inline"> poin</span></span>
                </div>
            </li>
        @empty
            <li class="card p-8 text-center border-2 border-dashed border-violet-200 bg-violet-50/40"><h3 class="font-semibold text-gray-900">Belum ada peringkat</h3><p class="mt-2 text-sm text-gray-600">Belum ada kehadiran jamaah yang memenuhi syarat pada rentang ini.</p></li>
        @endforelse
    </ol>
    <div class="mt-5">{{ $leaders->links() }}</div>
</div>
@endsection
