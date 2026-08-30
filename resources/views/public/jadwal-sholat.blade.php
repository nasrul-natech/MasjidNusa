@extends('layouts.public')

@section('title', 'Jadwal Sholat')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6">
    <div class="max-w-sm mx-auto">
        {{-- Header --}}
        <div class="bg-emerald-700 rounded-3xl p-6 text-white text-center mb-6 relative">
            @auth
            <a href="{{ route('user.adzan.settings') }}" class="absolute top-4 right-4 w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center hover:bg-white/30 transition-colors" title="Pengaturan Adzan">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </a>
            @endauth
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-xl font-bold">Jadwal Sholat</h1>
            <p class="text-emerald-200 text-sm mt-1">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
            <p class="text-emerald-300 text-xs mt-0.5">{{ \App\Helpers\HijriDate::format(now()->year, now()->month, now()->day) }}</p>
        </div>

        {{-- Location Selector --}}
        @if($locations->count() > 0)
        <x-prayer-location-detect :locations="$locations" />
        <div class="card mb-4 p-3">
            <form method="GET" action="{{ route('jadwal-sholat') }}" id="location-form" class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                <select name="location_id" id="jadwal-location-select" class="flex-1 text-sm border-0 bg-transparent focus:ring-0 p-0 text-gray-700">
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}"
                        data-city="{{ $loc->city_name }}"
                        {{ $selectedLocation && $selectedLocation->id == $loc->id ? 'selected' : '' }}>
                        {{ $loc->city_name }}, {{ $loc->province_name }}
                    </option>
                    @endforeach
                </select>
                <button type="button" id="jadwal-detect-gps"
                    class="shrink-0 flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 active:bg-emerald-200 transition-colors"
                    title="Gunakan lokasi saya">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06z"/>
                    </svg>
                    GPS
                </button>
            </form>
            <p id="jadwal-gps-status" class="text-[10px] text-gray-400 mt-1.5 hidden"></p>
        </div>
        <script>
        (function () {
            const detect = window.PrayerLocationDetect;
            const currentId = @json($selectedLocation?->id);
            const jadwalUrl = @json(route('jadwal-sholat'));
            const statusEl  = document.getElementById('jadwal-gps-status');
            const selectEl  = document.getElementById('jadwal-location-select');
            const gpsBtn    = document.getElementById('jadwal-detect-gps');
            const urlLocationId = new URLSearchParams(window.location.search).get('location_id');

            function showStatus(msg) {
                if (!statusEl) return;
                statusEl.textContent = msg;
                statusEl.classList.remove('hidden');
            }

            function goToLocation(locationId) {
                window.location.href = jadwalUrl + '?location_id=' + encodeURIComponent(locationId);
            }

            // Tombol GPS selalu aktif — daftarkan sebelum early return
            gpsBtn?.addEventListener('click', () => {
                detect.clearManualChoice();
                gpsBtn.disabled = true;
                showStatus('Meminta izin lokasi…');
                detect.autoDetect(showStatus)
                    .then(({ location }) => goToLocation(location.location_id))
                    .catch(err => showStatus(err.message || ''))
                    .finally(() => { gpsBtn.disabled = false; });
            });

            // Pilihan manual dari dropdown — jangan timpa dengan GPS
            selectEl?.addEventListener('change', () => {
                const opt = selectEl.options[selectEl.selectedIndex];
                detect.markManualPickPending();
                detect.setManualChoice(opt.value, opt.dataset.city || '');
                goToLocation(opt.value);
            });

            // User baru saja pilih manual via dropdown
            if (detect.consumeManualPickPending() && urlLocationId) {
                const opt = selectEl?.options[selectEl.selectedIndex];
                detect.setManualChoice(urlLocationId, opt?.dataset.city || '');
                return;
            }

            // User sudah pernah pilih manual — hormati pilihan, jangan auto-detect
            if (detect.isManualChoice()) {
                const saved = detect.getSavedLocation();
                if (saved.id && String(saved.id) !== String(currentId ?? '')) {
                    goToLocation(saved.id);
                }
                return;
            }

            // Auto-detect hanya saat buka pertama tanpa location_id di URL
            if (!urlLocationId) {
                detect.autoDetect(showStatus)
                    .then(({ location }) => goToLocation(location.location_id))
                    .catch(err => {
                        const saved = detect.getSavedLocation();
                        if (saved.id && String(saved.id) !== String(currentId ?? '')) {
                            goToLocation(saved.id);
                            return;
                        }
                        showStatus(err.message || '');
                    });
            }
        })();
        </script>
        @endif

        {{-- Imported Schedule (Location-based) --}}
        @if($todaySchedule && !$useFallback)
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900">Sholat Fardhu</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @php
                    $prayers = [
                        ['name' => 'Subuh', 'time' => $todaySchedule->subuh],
                        ['name' => 'Dzuhur', 'time' => $todaySchedule->dzuhur],
                        ['name' => 'Ashar', 'time' => $todaySchedule->ashar],
                        ['name' => 'Maghrib', 'time' => $todaySchedule->maghrib],
                        ['name' => 'Isya', 'time' => $todaySchedule->isya],
                    ];
                    $now = now()->format('H:i');
                    $nextFound = false;
                @endphp
                @foreach($prayers as $prayer)
                @php
                    $time = \Carbon\Carbon::parse($prayer['time'])->format('H:i');
                    $isNext = !$nextFound && $time >= $now;
                    if ($isNext) $nextFound = true;
                @endphp
                <div class="flex items-center justify-between px-5 py-4 {{ $isNext ? 'bg-emerald-50' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $isNext ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center">
                            <span class="text-base">🕌</span>
                        </div>
                        <span class="font-semibold text-gray-900 text-sm">{{ $prayer['name'] }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-bold {{ $isNext ? 'text-emerald-700' : 'text-gray-600' }} text-base">{{ $time }}</span>
                        @if($isNext)
                        <span class="ml-2 text-[10px] font-medium text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Berikutnya</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Additional times --}}
        @if($todaySchedule->imsak || $todaySchedule->terbit || $todaySchedule->dhuha)
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900">Waktu Lainnya</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @if($todaySchedule->imsak)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-700">Imsak</span>
                    <span class="font-mono text-sm text-gray-600">{{ \Carbon\Carbon::parse($todaySchedule->imsak)->format('H:i') }}</span>
                </div>
                @endif
                @if($todaySchedule->terbit)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-700">Terbit</span>
                    <span class="font-mono text-sm text-gray-600">{{ \Carbon\Carbon::parse($todaySchedule->terbit)->format('H:i') }}</span>
                </div>
                @endif
                @if($todaySchedule->dhuha)
                <div class="flex items-center justify-between px-5 py-3">
                    <span class="text-sm text-gray-700">Dhuha</span>
                    <span class="font-mono text-sm text-gray-600">{{ \Carbon\Carbon::parse($todaySchedule->dhuha)->format('H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        @else
        {{-- Fallback: Manual/Legacy Schedule --}}
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900">Sholat Fardhu</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($dailySchedules as $schedule)
                @php
                    $now = now()->format('H:i');
                    $time = \Carbon\Carbon::parse($schedule->time)->format('H:i');
                    $isNext = $time >= $now;
                @endphp
                <div class="flex items-center justify-between px-5 py-4 {{ $isNext ? 'bg-emerald-50' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $isNext ? 'bg-emerald-100' : 'bg-gray-100' }} flex items-center justify-center">
                            <span class="text-base">🕌</span>
                        </div>
                        <span class="font-semibold text-gray-900 text-sm">{{ $schedule->prayer_name }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-bold {{ $isNext ? 'text-emerald-700' : 'text-gray-600' }} text-base">{{ $time }}</span>
                        @if($isNext && $time >= $now)
                        <span class="ml-2 text-[10px] font-medium text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Berikutnya</span>
                        @break
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Jumat --}}
        @if($jumatSchedule)
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900">Sholat Jum'at</h2>
            </div>
            <div class="flex items-center justify-between px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
                        <span class="text-base">🕌</span>
                    </div>
                    <span class="font-semibold text-gray-900 text-sm">{{ $jumatSchedule->prayer_name }}</span>
                </div>
                <span class="font-bold text-amber-700 text-base">{{ \Carbon\Carbon::parse($jumatSchedule->time)->format('H:i') }}</span>
            </div>
        </div>
        @endif

        @if($selectedLocation)
        <p class="text-center text-xs text-gray-400 mt-4">Jadwal untuk {{ $selectedLocation->full_name }}</p>
        @endif
        <p class="text-center text-xs text-gray-400 mt-1">Jadwal dapat berubah sesuai kondisi</p>
    </div>
</div>
@endsection
