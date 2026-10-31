@extends('layouts.admin.operasional')
@section('title', 'Poin Kehadiran Jamaah')
@section('page-title', 'Poin Kehadiran Jamaah')
@section('content')
<div class="card overflow-hidden mb-5">
    <div class="card-header bg-gradient-to-r from-indigo-600 to-purple-600 border-0 flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-semibold text-white">Periode Poin Kehadiran</h2>
        <x-badge type="info" class="!bg-white/20 !text-white ring-1 ring-white/30">Rekap privat</x-badge>
    </div>
    <div class="card-body text-sm leading-relaxed text-gray-700 bg-gradient-to-b from-indigo-50/60 to-white">
        Rekap privat per periode. Satu kegiatan memberi satu poin. Hadiah dicatat manual, tanpa pembayaran otomatis.
    </div>
</div>
@if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 mb-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
@can('manage_attendance_rewards')
<div class="card overflow-hidden mb-6">
<div class="card-header bg-amber-50 flex flex-wrap items-center justify-between gap-2 border-b border-amber-100">
    <div><h2 class="font-semibold text-amber-950">Buat Periode Baru</h2><p class="mt-1 text-sm text-amber-800/80">Tentukan rentang tanggal dan jumlah penerima sebelum mengaktifkan periode.</p></div>
    <x-badge type="warning">Draft</x-badge>
</div>
<form method="POST" action="{{ route('admin.operasional.attendance-rewards.store') }}" class="card-body grid gap-4 sm:grid-cols-2 bg-amber-50/30">
    @csrf
    <div><label class="form-label" for="name">Nama periode</label><input id="name" name="name" value="{{ old('name') }}" maxlength="150" required class="form-input"></div>
    <div><label class="form-label" for="timezone">Zona waktu masjid</label><select id="timezone" name="timezone" class="form-input">@foreach(timezone_identifiers_list() as $timezone)<option value="{{ $timezone }}" @selected(old('timezone', config('app.timezone')) === $timezone)>{{ $timezone }}</option>@endforeach</select></div>
    <div><label class="form-label" for="start_date">Tanggal awal (inklusif)</label><input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}" required class="form-input"></div>
    <div><label class="form-label" for="end_date">Tanggal akhir (inklusif)</label><input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}" required class="form-input"></div>
    <div><label class="form-label" for="winner_count">Jumlah penerima</label><select id="winner_count" name="winner_count" class="form-input"><option value="3">3</option><option value="10" @selected(old('winner_count') == 10)>10</option></select></div>
    <div class="self-end"><button type="submit" class="btn-primary w-full sm:w-auto shadow-md">Buat Draft</button></div>
</form>
</div>
@endcan
<div class="flex flex-wrap items-center justify-between gap-2 mb-3">
    <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-800">Daftar Periode</h2>
    <x-badge type="purple">{{ $periods->total() }} periode</x-badge>
</div>
<div class="grid gap-4 lg:grid-cols-2">
@forelse($periods as $period)
@php($statusType = $period->status === 'final' ? 'info' : ($period->status === 'active' ? 'success' : 'gray'))
<a class="card overflow-hidden block hover:shadow-lg transition-shadow focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-600 border-l-4 {{ $period->status === 'active' ? 'border-l-emerald-500' : ($period->status === 'final' ? 'border-l-indigo-500' : 'border-l-gray-300') }}" href="{{ route('admin.operasional.attendance-rewards.show', $period) }}">
    <div class="card-header bg-gradient-to-r from-slate-50 to-indigo-50/80 flex items-start justify-between gap-3 py-3">
        <h3 class="font-semibold text-gray-900 break-words min-w-0">{{ $period->name }}</h3>
        <x-badge :type="$statusType">{{ ucfirst($period->status) }}</x-badge>
    </div>
    <div class="card-body pt-3">
    <p class="text-sm font-medium text-gray-800">{{ $period->start_date->format('d/m/Y') }} – {{ $period->end_date->format('d/m/Y') }}</p>
    <p class="mt-1 text-xs text-gray-500 break-words">{{ $period->timezone }}</p>
    <div class="mt-4 pt-3 border-t border-indigo-100 flex flex-wrap items-center justify-between gap-2 text-sm">
        <span class="inline-flex items-center gap-2"><x-badge type="info">{{ $period->winner_count }} penerima</x-badge></span>
        <span class="font-bold text-indigo-700">Lihat Rekap &rsaquo;</span>
    </div>
    </div>
</a>
@empty<div class="card overflow-hidden p-8 text-center lg:col-span-2 border-2 border-dashed border-indigo-200 bg-indigo-50/30"><h3 class="font-semibold text-gray-900">Belum ada periode hadiah.</h3><p class="mt-2 text-sm text-gray-600">Periode yang dibuat akan tampil di sini beserta status dan rekap poinnya.</p></div>@endforelse
</div>
<div class="mt-5">{{ $periods->links() }}</div>
@endsection
