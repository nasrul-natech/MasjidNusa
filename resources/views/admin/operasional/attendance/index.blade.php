@extends('layouts.admin.operasional')
@section('title', 'Absensi Kegiatan')
@section('page-title', 'Absensi Kegiatan')
@section('content')
<div class="card overflow-hidden mb-5">
    <div class="card-header bg-gradient-to-r from-teal-600 to-emerald-600 border-0 flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-semibold text-white">Kelola Check-in Kegiatan</h2>
        <x-badge type="success" class="!bg-white/20 !text-white ring-1 ring-white/30">Operasional</x-badge>
    </div>
    <div class="card-body text-sm leading-relaxed text-gray-700 bg-gradient-to-b from-teal-50/80 to-white">
        Selesaikan kegiatan untuk membuka check-in. Tombol tidak mengubah status publikasi dan tidak dapat memperpanjang batas waktu.
    </div>
</div>
@if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 mb-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
<form method="GET" class="card overflow-hidden mb-5">
    <div class="card-header bg-sky-50 flex flex-wrap items-center justify-between gap-2">
        <h2 class="text-sm font-semibold text-sky-900">Cari kegiatan</h2>
        <x-badge type="info">Filter</x-badge>
    </div>
    <div class="card-body admin-table-toolbar flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1 min-w-0"><label for="attendance-search" class="form-label">Judul kegiatan</label><input id="attendance-search" name="search" value="{{ request('search') }}" class="form-input" placeholder="Ketik judul..."></div>
        <button type="submit" class="btn-primary shrink-0 shadow-sm">Cari</button>
    </div>
</form>
<div class="space-y-4">
@forelse($activities as $activity)
@php($session = $activity->attendanceSession)
@php($checkOpen = $session && now()->lt($session->closes_at))
<article class="card overflow-hidden shadow-sm">
    <div class="card-header bg-gradient-to-r from-emerald-50 to-teal-50 flex flex-wrap items-start justify-between gap-3 border-b border-emerald-100">
        <h2 class="font-semibold text-gray-900 break-words min-w-0 flex-1">{{ $activity->title }}</h2>
        <div class="flex flex-wrap gap-2 shrink-0">
            <x-badge :type="$activity->status === 'publish' ? 'success' : 'warning'">{{ $activity->status === 'publish' ? 'Publish' : ucfirst($activity->status) }}</x-badge>
            @if($session)
                <x-badge :type="$checkOpen ? 'success' : 'gray'">{{ $checkOpen ? 'Check-in terbuka' : 'Check-in ditutup' }}</x-badge>
            @endif
        </div>
    </div>
    <div class="card-body space-y-4">
    <dl class="grid gap-3 sm:grid-cols-2 text-sm">
        <div class="rounded-xl bg-white border border-gray-100 p-3"><dt class="text-xs font-semibold uppercase tracking-wide text-teal-700">Mulai kegiatan</dt><dd class="mt-1 font-semibold text-gray-900">{{ $activity->start_date->format('d/m/Y H:i') }}</dd></div>
        <div class="rounded-xl bg-white border border-gray-100 p-3"><dt class="text-xs font-semibold uppercase tracking-wide text-sky-700">Durasi check-in</dt><dd class="mt-1 font-semibold text-gray-900">{{ $activity->attendanceSetting->duration_seconds / 60 }} menit</dd></div>
    </dl>
    @if($session)
        <div class="rounded-xl border-2 {{ $checkOpen ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-gray-50' }} p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <x-badge :type="$checkOpen ? 'success' : 'gray'">{{ $checkOpen ? 'Sesi aktif' : 'Sesi selesai' }}</x-badge>
                <p class="text-sm text-gray-700">Batas <strong class="text-gray-900">{{ $session->closes_at->format('d/m/Y H:i:s') }}</strong></p>
            </div>
            <div class="rounded-xl bg-white/80 border border-emerald-100 px-4 py-2 text-center sm:text-right">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Hadir</p>
                <p class="text-2xl font-bold text-emerald-700 tabular-nums">{{ $session->attendances_count }}</p>
            </div>
        </div>
    @elseif($activity->status === 'publish' && now()->gte($activity->start_date))
        <form method="POST" action="{{ route('admin.operasional.attendance.open', $activity) }}" onsubmit="return confirm('Selesaikan kegiatan ini dan buka check-in selama {{ $activity->attendanceSetting->duration_seconds / 60 }} menit? Jendela tidak dapat dibuka ulang.')">
            @csrf<button type="submit" class="btn-primary w-full sm:w-auto shadow-md">Selesaikan &amp; Buka Check-in</button>
        </form>
    @else
        <p class="rounded-xl bg-amber-50 border-2 border-amber-200 p-3 text-sm text-amber-900"><x-badge type="warning" class="mr-2">Menunggu</x-badge> Menunggu waktu mulai dan publikasi kegiatan.</p>
    @endif
    </div>
</article>
@empty
<div class="card overflow-hidden p-8 text-center border-2 border-dashed border-gray-200">
    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-teal-100 flex items-center justify-center">@include('components.icons.users', ['class' => 'w-7 h-7 text-teal-700'])</div>
    <h2 class="font-semibold text-gray-900">Tidak ada kegiatan ditemukan</h2><p class="mt-2 text-sm text-gray-500">Kegiatan dengan absensi aktif akan tampil di sini. Jika memakai pencarian, coba kata kunci lain.</p>
</div>
@endforelse
</div>
<div class="mt-4">{{ $activities->links() }}</div>
@endsection
