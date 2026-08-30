@extends('layouts.admin.operasional')

@section('title', 'Jadwal Sholat')
@section('page-title', 'Jadwal Sholat')

@section('content')
{{-- Action Buttons --}}
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('admin.operasional.prayer-schedule.import-form') }}" class="btn-primary text-sm">
        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Ambil / Import Jadwal
    </a>
    <a href="{{ route('admin.operasional.prayer-schedule.locations') }}" class="btn-secondary text-sm">
        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Kelola Lokasi
    </a>
    <a href="{{ route('admin.operasional.prayer-schedule.create') }}" class="btn-secondary text-sm">+ Jadwal Manual</a>
</div>

{{-- Location & Month Filter --}}
@if($locations->count())
<div class="card p-4 mb-5">
    <form method="GET" action="{{ route('admin.operasional.prayer-schedule.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="form-label text-xs">Lokasi</label>
            <select name="location_id" class="form-input text-sm">
                <option value="">-- Pilih Lokasi --</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ $selectedLocationId == $loc->id ? 'selected' : '' }}>
                    {{ $loc->city_name }}, {{ $loc->province_name }} {{ $loc->is_default ? '(Default)' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="w-32">
            <label class="form-label text-xs">Bulan</label>
            <select name="month" class="form-input text-sm">
                @foreach(range(1,12) as $m)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-24">
            <label class="form-label text-xs">Tahun</label>
            <select name="year" class="form-input text-sm">
                @foreach(range(now()->year - 1, now()->year + 2) as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm h-[38px]">Tampilkan</button>
    </form>
</div>
@endif

{{-- Imported Prayer Times Table --}}
@if($selectedLocation && $prayerTimes->count())
<div class="card overflow-hidden mb-6">
    <div class="card-header flex justify-between items-center">
        @php
            $firstHijri = \App\Helpers\HijriDate::fromGregorian($year, $month, $prayerTimes->first()->date->day);
            $lastHijri  = \App\Helpers\HijriDate::fromGregorian($year, $month, $prayerTimes->last()->date->day);
            $hijriRange  = $firstHijri['day'] . ' ' . $firstHijri['month_name'];
            if ($firstHijri['month'] !== $lastHijri['month']) {
                $hijriRange .= ' – ' . $lastHijri['day'] . ' ' . $lastHijri['month_name'];
            } else {
                $hijriRange .= ' – ' . $lastHijri['day'];
            }
            $hijriRange .= ' ' . $lastHijri['year'] . ' H';
        @endphp
        <div>
            <h3 class="font-semibold text-gray-900">{{ $selectedLocation->city_name }}, {{ $selectedLocation->province_name }}</h3>
            <p class="text-xs text-gray-500">{{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }} &middot; {{ $prayerTimes->count() }} hari</p>
            <p class="text-xs text-emerald-600 mt-0.5">{{ $hijriRange }}</p>
        </div>
        <form action="{{ route('admin.operasional.prayer-schedule.destroy-month') }}" method="POST"
              onsubmit="return confirm('Hapus semua jadwal bulan ini untuk lokasi ini?')">
            @csrf @method('DELETE')
            <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="btn-action-red">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus Bulan Ini
            </button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="admin-table text-xs">
            <thead>
                <tr>
                    <th class="w-12">Tgl</th>
                    <th class="w-20">Hijriah</th>
                    <th>Imsak</th>
                    <th>Subuh</th>
                    <th>Terbit</th>
                    <th>Dhuha</th>
                    <th>Dzuhur</th>
                    <th>Ashar</th>
                    <th>Maghrib</th>
                    <th>Isya</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prayerTimes as $pt)
                @php $hijri = \App\Helpers\HijriDate::fromGregorian($pt->date->year, $pt->date->month, $pt->date->day); @endphp
                <tr class="{{ $pt->date->isToday() ? 'bg-emerald-50 font-semibold' : '' }}">
                    <td class="text-center font-medium">
                        {{ $pt->date->format('d') }}
                        @if($pt->date->isToday()) <span class="text-[9px] text-emerald-600 block">Hari Ini</span> @endif
                    </td>
                    <td class="text-center text-[10px] text-gray-500 leading-tight">
                        {{ $hijri['day'] }}
                        @if($hijri['day'] === 1)<span class="block text-emerald-600 font-semibold">{{ $hijri['month_name'] }}</span>@endif
                    </td>
                    <td class="font-mono text-center">{{ $pt->imsak ? Carbon\Carbon::parse($pt->imsak)->format('H:i') : '-' }}</td>
                    <td class="font-mono text-center text-emerald-700">{{ Carbon\Carbon::parse($pt->subuh)->format('H:i') }}</td>
                    <td class="font-mono text-center">{{ $pt->terbit ? Carbon\Carbon::parse($pt->terbit)->format('H:i') : '-' }}</td>
                    <td class="font-mono text-center">{{ $pt->dhuha ? Carbon\Carbon::parse($pt->dhuha)->format('H:i') : '-' }}</td>
                    <td class="font-mono text-center text-emerald-700">{{ Carbon\Carbon::parse($pt->dzuhur)->format('H:i') }}</td>
                    <td class="font-mono text-center text-emerald-700">{{ Carbon\Carbon::parse($pt->ashar)->format('H:i') }}</td>
                    <td class="font-mono text-center text-emerald-700">{{ Carbon\Carbon::parse($pt->maghrib)->format('H:i') }}</td>
                    <td class="font-mono text-center text-emerald-700">{{ Carbon\Carbon::parse($pt->isya)->format('H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif($selectedLocation)
<div class="card p-6 text-center mb-6">
    <p class="text-gray-500 mb-3">Belum ada data jadwal untuk <strong>{{ $selectedLocation->city_name }}</strong>
        ({{ DateTime::createFromFormat('!m', $month)->format('F') }} {{ $year }}).</p>
    <div class="flex flex-wrap justify-center gap-2">
        <form action="{{ route('admin.operasional.prayer-schedule.fetch-api') }}" method="POST">
            @csrf
            <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="btn-primary text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Ambil Data Otomatis
            </button>
        </form>
        <a href="{{ route('admin.operasional.prayer-schedule.import-form') }}" class="btn-secondary text-sm">Import CSV</a>
    </div>
</div>
@elseif($locations->count() === 0)
<div class="card p-8 text-center mb-6">
    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
    </div>
    <p class="text-gray-500 font-medium">Belum ada lokasi terdaftar</p>
    <p class="text-gray-400 text-sm mt-1">Tambahkan lokasi terlebih dahulu untuk mulai mengimpor jadwal sholat</p>
    <a href="{{ route('admin.operasional.prayer-schedule.locations') }}" class="btn-primary text-sm mt-3 inline-block">+ Tambah Lokasi</a>
</div>
@endif

{{-- Manual Schedules (Legacy) --}}
@if($manualSchedules->count())
<div class="card overflow-hidden">
    <div class="card-header flex justify-between items-center">
        <div>
            <h3 class="font-semibold text-gray-900">Jadwal Manual / Khusus</h3>
            <p class="text-xs text-gray-500">Jadwal yang diinput secara manual (Jum'at, khusus, dll)</p>
        </div>
        <a href="{{ route('admin.operasional.prayer-schedule.create') }}" class="text-sm text-emerald-600 hover:underline">+ Tambah</a>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Nama Sholat</th>
                <th>Waktu</th>
                <th>Tipe</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($manualSchedules as $schedule)
            <tr>
                <td class="font-semibold">{{ $schedule->prayer_name }}</td>
                <td class="font-mono">{{ \Carbon\Carbon::parse($schedule->time)->format('H:i') }}</td>
                <td><x-badge type="{{ $schedule->type === 'daily' ? 'success' : ($schedule->type === 'jumat' ? 'warning' : 'info') }}">{{ ucfirst($schedule->type) }}</x-badge></td>
                <td><x-badge type="{{ $schedule->is_active ? 'success' : 'gray' }}">{{ $schedule->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                <td>
                    <div class="action-group">
                        <a href="{{ route('admin.operasional.prayer-schedule.edit', $schedule->id) }}" class="btn-action-blue">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                        <form action="{{ route('admin.operasional.prayer-schedule.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-action-red">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
