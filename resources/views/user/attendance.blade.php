@extends('layouts.public')
@section('title', 'Kehadiran Saya')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-6 sm:py-8 user-page-shell">
    <div class="rounded-2xl overflow-hidden shadow-lg mb-4 sm:mb-6 bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 text-white p-4 sm:p-6">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-white/75">Jamaah</p>
                <h1 class="text-xl sm:text-3xl font-bold mt-0.5">Kehadiran Saya</h1>
                <p class="mt-1 text-xs sm:text-sm text-white/90 line-clamp-2">Riwayat check-in dan poin kegiatan.</p>
            </div>
            <x-badge type="success" class="!bg-white/20 !text-white ring-1 ring-white/40 shrink-0 !text-[10px] sm:!text-xs whitespace-nowrap">1=1 poin</x-badge>
        </div>
    </div>
    <div class="mb-4 sm:mb-6">
        <a href="{{ route('user.attendance.leaderboard') }}" class="btn-primary text-center shadow-sm w-full sm:w-auto !py-2 text-xs sm:text-sm">Peringkat Kehadiran</a>
    </div>
    <div class="mb-4 sm:mb-6">
        <x-stat-card variant="filled" label="Total historis" :value="$points . ' poin'" iconName="calendar" color="emerald" />
        <p class="mt-2 hidden sm:block text-sm text-gray-600">Poin periode hadiah dapat berbeda dari total historis.</p>
    </div>
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <h2 class="text-sm font-bold uppercase tracking-wide text-teal-800">Riwayat Kehadiran</h2>
        @if($attendances->total() > 0)<x-badge type="success">{{ $attendances->total() }} catatan</x-badge>@endif
    </div>
    <div class="space-y-3">
    @forelse($attendances as $attendance)
        <article class="card overflow-hidden flex items-center shadow-sm border-l-4 border-l-emerald-500 min-h-[2.75rem]">
            <div class="flex-1 min-w-0 py-2 pl-2 pr-1 sm:p-4 bg-gradient-to-r from-emerald-50/90 to-white">
                <p class="truncate font-semibold text-gray-900 text-xs sm:text-sm" title="{{ $attendance->session->activity->title }}">{{ $attendance->session->activity->title }}</p>
                <p class="mt-0.5 truncate text-[10px] sm:text-xs text-emerald-800/90 tabular-nums">{{ $attendance->checked_in_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="shrink-0 flex items-center px-2 sm:px-4 self-stretch bg-emerald-600 text-white">
                <span class="text-[10px] sm:text-sm font-bold whitespace-nowrap">+1</span>
            </div>
        </article>
    @empty
        <div class="card overflow-hidden p-8 text-center border-2 border-dashed border-emerald-200 bg-emerald-50/40">
            <h3 class="font-semibold text-gray-900">Belum ada riwayat kehadiran</h3>
            <p class="mt-2 text-sm leading-relaxed text-gray-600">Check-in pada halaman kegiatan saat absensi dibuka. Kehadiran yang tercatat akan tampil di sini.</p>
        </div>
    @endforelse
    </div>
    <div class="mt-5">{{ $attendances->links() }}</div>
</div>
@endsection
