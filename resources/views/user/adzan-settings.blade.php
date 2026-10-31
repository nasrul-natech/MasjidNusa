@extends('layouts.public')

@section('title', 'Pengaturan Adzan')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6 user-page-shell">
    <div class="max-w-md mx-auto">

        {{-- Header --}}
        <div class="bg-emerald-700 rounded-3xl p-6 text-white text-center mb-6">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold">Pengaturan Adzan</h1>
            <p class="text-emerald-200 text-sm mt-1">Atur notifikasi & suara adzan sesuai keinginan</p>
        </div>

        <form action="{{ route('user.adzan.settings.update') }}" method="POST" id="adzan-form">
            @csrf

            {{-- Lokasi (sinkron otomatis dengan Jadwal Sholat) --}}
            @if($locations->count())
            <x-prayer-location-detect :locations="$locations" />
            @endif
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        Lokasi
                    </h2>
                </div>
                <div class="px-5 py-4">
                    <input type="hidden" name="location_id" id="adzan-location-id" value="{{ $activeLocation?->id ?? $settings->location_id }}">
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                        <div class="w-9 h-9 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p id="adzan-location-label" class="text-sm font-semibold text-gray-900 truncate">
                                {{ $activeLocation?->city_name ?? 'Memuat lokasi…' }}
                            </p>
                            <p id="adzan-location-province" class="text-xs text-gray-500 truncate">
                                {{ $activeLocation?->province_name ?? '' }}
                            </p>
                        </div>
                        <span class="text-[10px] font-medium text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full shrink-0">Sinkron</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        Mengikuti lokasi Jadwal Sholat secara otomatis.
                        <a href="{{ route('jadwal-sholat') }}" class="text-emerald-600 font-medium hover:text-emerald-700">Ubah lokasi →</a>
                    </p>
                </div>
            </div>

            {{-- Jadwal Hari Ini (preview, updated via JS on location change) --}}
            <div class="card mb-4" id="schedule-preview-card">
                <div class="card-header">
                    <h2 class="font-semibold text-gray-900 text-sm">Jadwal Hari Ini</h2>
                </div>
                <div class="px-5 py-3">
                    <div id="schedule-grid" class="grid grid-cols-3 gap-2 text-center">
                        @if($todaySchedule)
                        @foreach([
                            ['Subuh', $todaySchedule->subuh],
                            ['Dzuhur', $todaySchedule->dzuhur],
                            ['Ashar', $todaySchedule->ashar],
                            ['Maghrib', $todaySchedule->maghrib],
                            ['Isya', $todaySchedule->isya],
                            ['Imsak', $todaySchedule->imsak],
                        ] as [$name, $time])
                        <div class="bg-emerald-50 rounded-xl py-2 px-1">
                            <p class="text-[10px] text-emerald-600 font-medium">{{ $name }}</p>
                            <p class="text-sm font-bold text-emerald-800">{{ substr($time, 0, 5) }}</p>
                        </div>
                        @endforeach
                        @else
                        <div class="col-span-3 text-center text-gray-400 text-xs py-2">Pilih lokasi untuk melihat jadwal</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Aktifkan Notifikasi --}}
            <div class="card mb-4" id="push-permission-card">
                <div class="card-header">
                    <div class="flex items-center justify-between w-full">
                        <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            Push Notification
                        </h2>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="push_enabled" value="0">
                            <input type="checkbox" name="push_enabled" value="1" {{ $settings->push_enabled ? 'checked' : '' }}
                                class="sr-only peer" id="push-toggle">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </div>
                <div class="px-5 py-3 space-y-3">
                    <p class="text-xs text-gray-500">Notifikasi muncul di browser/HP meskipun tab tertutup (perlu izin browser).</p>

                    {{-- Banner izin — tampil di halaman ini, bukan saat buka web --}}
                    <div id="push-permission-banner" class="hidden rounded-2xl border-2 border-amber-300 bg-amber-50 p-4">
                        <p class="text-sm font-semibold text-amber-900 mb-1">Izin Notifikasi Diperlukan</p>
                        <p class="text-xs text-amber-800 mb-3">Aktifkan izin notifikasi browser agar reminder adzan berfungsi meski tab ditutup.</p>
                        <button type="button" id="enable-push-btn"
                            class="w-full bg-emerald-700 text-white font-semibold py-2.5 rounded-xl text-sm hover:bg-emerald-800 active:bg-emerald-900 transition-colors">
                            Izinkan Notifikasi
                        </button>
                    </div>

                    <p id="push-status" class="text-xs font-medium hidden"></p>

                    <div id="push-next-info" class="hidden rounded-xl bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                        <span class="font-semibold">Sholat berikutnya:</span>
                        <span id="push-next-label">—</span>
                    </div>
                </div>
            </div>

            {{-- Pilih Waktu Sholat --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Waktu Sholat
                    </h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach([
                        ['imsak',   'Imsak',   '☽', 'Sebelum Subuh'],
                        ['subuh',   'Subuh',   '🌅', 'Sholat Fardhu'],
                        ['dzuhur',  'Dzuhur',  '☀️', 'Sholat Fardhu'],
                        ['ashar',   'Ashar',   '🌤', 'Sholat Fardhu'],
                        ['maghrib', 'Maghrib', '🌇', 'Sholat Fardhu'],
                        ['isya',    'Isya',    '🌙', 'Sholat Fardhu'],
                    ] as [$key, $name, $icon, $desc])
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <span class="text-lg w-6 text-center">{{ $icon }}</span>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $name }}</p>
                                <p class="text-[10px] text-gray-400">{{ $desc }}</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="{{ $key }}_enabled" value="0">
                            <input type="checkbox" name="{{ $key }}_enabled" value="1"
                                {{ $settings->{$key . '_enabled'} ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Suara Adzan --}}
            <div class="card mb-4">
                <div class="card-header">
                    <div class="flex items-center justify-between w-full">
                        <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                            Suara Adzan
                        </h2>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="sound_enabled" value="0">
                            <input type="checkbox" name="sound_enabled" value="1" {{ $settings->sound_enabled ? 'checked' : '' }}
                                class="sr-only peer" id="sound-toggle">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </div>
                <div class="px-5 py-4 space-y-4" id="sound-options">
                    <div class="space-y-2">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pilih Suara</p>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach([
                                ['simple', '🔔', 'Sederhana'],
                                ['makkah', '🕋', 'Makkah'],
                                ['madinah', '🕌', 'Madinah'],
                            ] as [$type, $tIcon, $label])
                            <label class="cursor-pointer">
                                <input type="radio" name="sound_type" value="{{ $type }}"
                                    {{ $settings->sound_type === $type ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="text-center py-3 px-2 rounded-xl border-2 border-gray-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all">
                                    <span class="text-2xl">{{ $tIcon }}</span>
                                    <p class="text-xs font-semibold mt-1 text-gray-700 peer-checked:text-emerald-700">{{ $label }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Volume</p>
                            <span id="volume-display" class="text-xs font-bold text-emerald-700">{{ $settings->volume }}%</span>
                        </div>
                        <input type="range" name="volume" id="volume-slider" min="0" max="100" step="5"
                            value="{{ $settings->volume }}"
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-600">
                    </div>

                    <p id="active-sound-label" class="text-xs font-semibold text-emerald-700">Suara aktif: {{ ['simple' => 'Sederhana', 'makkah' => 'Makkah', 'madinah' => 'Madinah'][$settings->sound_type] ?? 'Sederhana' }}</p>
                    <button type="button" id="preview-sound" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-emerald-200 text-emerald-700 font-semibold text-sm hover:bg-emerald-50 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Preview Suara
                    </button>
                </div>
            </div>

            {{-- Opsi Lanjutan --}}
            <div class="card mb-6">
                <div class="card-header">
                    <h2 class="font-semibold text-gray-900 text-sm">Opsi Lanjutan</h2>
                </div>
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700">Ingatkan</span>
                        <select name="minutes_before" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-emerald-500">
                            <option value="0" {{ $settings->minutes_before == 0 ? 'selected' : '' }}>Tepat waktu</option>
                            <option value="5" {{ $settings->minutes_before == 5 ? 'selected' : '' }}>5 menit</option>
                            <option value="10" {{ $settings->minutes_before == 10 ? 'selected' : '' }}>10 menit</option>
                            <option value="15" {{ $settings->minutes_before == 15 ? 'selected' : '' }}>15 menit</option>
                        </select>
                        <span class="text-sm text-gray-700">sebelum adzan</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-emerald-700 text-white font-bold py-3.5 rounded-2xl text-sm hover:bg-emerald-800 active:bg-emerald-900 transition-colors shadow-lg shadow-emerald-700/30">
                💾 Simpan Pengaturan
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('jadwal-sholat') }}" class="text-sm text-emerald-600 font-medium hover:text-emerald-700">← Kembali ke Jadwal Sholat</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $adzanClientSettings = [
        'location_id'     => $activeLocation?->id ?? $settings->location_id,
        'imsak_enabled'   => $settings->imsak_enabled,
        'subuh_enabled'   => $settings->subuh_enabled,
        'dzuhur_enabled'  => $settings->dzuhur_enabled,
        'ashar_enabled'   => $settings->ashar_enabled,
        'maghrib_enabled' => $settings->maghrib_enabled,
        'isya_enabled'    => $settings->isya_enabled,
        'sound_enabled'   => $settings->sound_enabled,
        'sound_type'      => $settings->sound_type,
        'volume'          => $settings->volume,
        'push_enabled'    => $settings->push_enabled,
        'minutes_before'  => $settings->minutes_before,
    ];
    $locationMapJson = $locations->mapWithKeys(fn ($loc) => [
        $loc->id => ['city' => $loc->city_name, 'province' => $loc->province_name],
    ]);
@endphp
<script>
(function () {
    const serverSettings = @json($adzanClientSettings);
    const locationMap = @json($locationMapJson);
    const syncLocationUrl = @json(route('user.adzan.settings.sync-location'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const locationInput = document.getElementById('adzan-location-id');
    const locationLabel = document.getElementById('adzan-location-label');
    const locationProvince = document.getElementById('adzan-location-province');

    function applyLocationDisplay(locId, cityFallback) {
        if (!locId || !locationInput) return;
        locationInput.value = locId;
        const info = locationMap[locId];
        if (locationLabel) locationLabel.textContent = info?.city || cityFallback || 'Lokasi tidak ditemukan';
        if (locationProvince) locationProvince.textContent = info?.province || '';
    }

    function syncLocationFromJadwal() {
        const prayerLoc = window.PrayerLocationDetect?.getSavedLocation?.() || {
            id: localStorage.getItem('prayer_location_id'),
            city: localStorage.getItem('prayer_location_city'),
        };

        const locId = prayerLoc.id || locationInput?.value;
        if (!locId) {
            if (locationLabel) locationLabel.textContent = 'Belum ada lokasi';
            return Promise.resolve(null);
        }

        applyLocationDisplay(locId, prayerLoc.city);
        loadSchedule(locId);

        // Jadwal Sholat → Adzan: simpan ke server jika berbeda
        if (String(locId) !== String(serverSettings.location_id)) {
            serverSettings.location_id = parseInt(locId, 10);
            if (window.AdzanClient) {
                AdzanClient.applyServerSettings({ location_id: serverSettings.location_id });
            }
            return fetch(syncLocationUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ location_id: locId }),
            }).catch(() => null);
        }

        // Adzan → Jadwal Sholat: isi localStorage jika belum ada
        if (!prayerLoc.id && window.PrayerLocationDetect) {
            const info = locationMap[locId];
            window.PrayerLocationDetect.saveLocation(locId, info?.city || '');
        }

        return Promise.resolve(null);
    }

    const soundNames = { simple: 'Sederhana', makkah: 'Makkah', madinah: 'Madinah' };
    function refreshActiveSoundLabel() {
        const label = document.getElementById('active-sound-label');
        if (label) label.textContent = 'Suara aktif: ' + (soundNames[selectedSoundType()] || 'Sederhana');
    }
    document.querySelectorAll('#adzan-form input[name="sound_type"]').forEach((input) => {
        input.addEventListener('change', () => {
            refreshActiveSoundLabel();
            if (window.AdzanClient) {
                AdzanClient.updateSettingsFromForm(collectFormSettings());
            }
        });
    });

    syncLocationFromJadwal().then(() => {
        if (window.AdzanClient) {
            AdzanClient.applyServerSettings(serverSettings);
            AdzanClient.reload().then(updateNextPrayerInfo);
        }
    });
    document.addEventListener('DOMContentLoaded', () => {
        if (window.AdzanClient) {
            AdzanClient.applyServerSettings({ ...serverSettings, sound_type: selectedSoundType() });
        }
        refreshActiveSoundLabel();
    });

    const slider = document.getElementById('volume-slider');
    const display = document.getElementById('volume-display');
    slider?.addEventListener('input', () => {
        display.textContent = slider.value + '%';
    });

    const scheduleGrid = document.getElementById('schedule-grid');
    const scheduleUrl = @json(route('adzan.schedule.json'));
    const minutesSelect = document.querySelector('select[name="minutes_before"]');
    const PRAYER_ORDER = [
        ['subuh', 'Subuh'], ['dzuhur', 'Dzuhur'], ['ashar', 'Ashar'],
        ['maghrib', 'Maghrib'], ['isya', 'Isya'], ['imsak', 'Imsak'],
    ];

    function renderSchedule(data) {
        if (!data?.schedule) {
            scheduleGrid.innerHTML = '<div class="col-span-3 text-center text-amber-500 text-xs py-2">Belum ada jadwal untuk lokasi ini</div>';
            return;
        }
        scheduleGrid.innerHTML = PRAYER_ORDER.map(([key, name]) => {
            const time = data.schedule[key] || '--:--';
            return `<div class="bg-emerald-50 rounded-xl py-2 px-1">
                <p class="text-[10px] text-emerald-600 font-medium">${name}</p>
                <p class="text-sm font-bold text-emerald-800">${time}</p>
            </div>`;
        }).join('');
        updateNextPrayerInfo(data);
    }

    function loadSchedule(locId) {
        if (!locId) {
            scheduleGrid.innerHTML = '<div class="col-span-3 text-center text-gray-400 text-xs py-2">Atur lokasi di Jadwal Sholat terlebih dahulu</div>';
            return;
        }
        scheduleGrid.innerHTML = '<div class="col-span-3 text-center text-gray-400 text-xs py-2">Memuat jadwal...</div>';
        fetch(scheduleUrl + '?location_id=' + locId)
            .then(r => r.json())
            .then(renderSchedule)
            .catch(() => {
                scheduleGrid.innerHTML = '<div class="col-span-3 text-center text-red-500 text-xs py-2">Gagal memuat jadwal</div>';
            });
    }

    function updateNextPrayerInfo(data) {
        const infoEl = document.getElementById('push-next-info');
        const labelEl = document.getElementById('push-next-label');
        const schedule = data?.schedule || window.AdzanClient?.getCurrentSchedule?.();
        if (!infoEl || !labelEl || !schedule) return;

        const now = new Date();
        const minutesBefore = parseInt(minutesSelect?.value || serverSettings.minutes_before || 0, 10);
        const enabledKeys = ['imsak','subuh','dzuhur','ashar','maghrib','isya'].filter(k => {
            const cb = document.querySelector(`input[name="${k}_enabled"]`);
            return cb ? cb.checked : serverSettings[k + '_enabled'];
        });

        let next = null;
        enabledKeys.forEach(key => {
            const timeStr = schedule[key];
            if (!timeStr || timeStr === '--:--') return;
            const [h, m] = timeStr.split(':').map(Number);
            const target = new Date();
            target.setHours(h, m, 0, 0);
            const diff = target - now;
            if (diff > 0 && (!next || diff < next.diff)) {
                next = { key, timeStr, diff };
            }
        });

        if (!next) {
            infoEl.classList.add('hidden');
            return;
        }

        const mins = Math.floor(next.diff / 60000);
        const hrs = Math.floor(mins / 60);
        const rem = mins % 60;
        const countdown = hrs > 0 ? `${hrs}j ${rem}m` : `${rem} menit`;
        const names = { imsak:'Imsak', subuh:'Subuh', dzuhur:'Dzuhur', ashar:'Ashar', maghrib:'Maghrib', isya:'Isya' };
        const reminder = minutesBefore > 0 ? ` (reminder ${minutesBefore} mnt sebelumnya)` : '';
        labelEl.textContent = `${names[next.key]} ${next.timeStr} — ${countdown} lagi${reminder}`;
        infoEl.classList.remove('hidden');
    }

    minutesSelect?.addEventListener('change', () => updateNextPrayerInfo());

    // Push notification permission — diminta di halaman ini
    const pushBtn = document.getElementById('enable-push-btn');
    const pushBanner = document.getElementById('push-permission-banner');
    const pushStatus = document.getElementById('push-status');
    const pushToggle = document.getElementById('push-toggle');

    function setPushStatus(msg, type) {
        if (!pushStatus) return;
        pushStatus.textContent = msg;
        pushStatus.classList.remove('hidden', 'text-emerald-600', 'text-red-500', 'text-amber-600');
        pushStatus.classList.add(type === 'error' ? 'text-red-500' : type === 'warn' ? 'text-amber-600' : 'text-emerald-600');
    }

    function checkPushState() {
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            setPushStatus('Browser tidak mendukung push notification', 'error');
            pushBanner?.classList.add('hidden');
            return;
        }

        const perm = Notification.permission;

        if (perm === 'granted') {
            setPushStatus('✓ Izin notifikasi aktif — push notification siap digunakan', 'ok');
            pushBanner?.classList.add('hidden');
            if (pushToggle?.checked) registerPushSubscription();
        } else if (perm === 'denied') {
            setPushStatus('✕ Notifikasi diblokir browser. Buka pengaturan browser untuk mengizinkan.', 'error');
            pushBanner?.classList.add('hidden');
        } else {
            setPushStatus('', 'ok');
            pushStatus?.classList.add('hidden');
            if (pushToggle?.checked) {
                pushBanner?.classList.remove('hidden');
            } else {
                pushBanner?.classList.add('hidden');
            }
        }
    }

    async function requestPushPermission() {
        if (!window.masjidNusaPush?.requestPermission) {
            setPushStatus('Push manager belum siap, muat ulang halaman', 'error');
            return false;
        }

        pushBtn.disabled = true;
        pushBtn.textContent = 'Meminta izin…';

        try {
            await window.masjidNusaPush.requestPermission();
            checkPushState();
            return Notification.permission === 'granted';
        } catch (e) {
            setPushStatus('Gagal mengaktifkan notifikasi', 'error');
            return false;
        } finally {
            pushBtn.disabled = false;
            pushBtn.textContent = 'Izinkan Notifikasi';
        }
    }

    async function registerPushSubscription() {
        try {
            const reg = await window.masjidNusaPush?.registerServiceWorker?.();
            if (!reg) return;

            const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
            if (!vapidKey) {
                setPushStatus('VAPID key belum dikonfigurasi di server', 'warn');
                return;
            }

            let sub = await reg.pushManager.getSubscription();
            if (!sub) {
                const padding = '='.repeat((4 - vapidKey.length % 4) % 4);
                const base64 = (vapidKey + padding).replace(/-/g, '+').replace(/_/g, '/');
                const raw = window.atob(base64);
                const key = new Uint8Array(raw.length);
                for (let i = 0; i < raw.length; i++) key[i] = raw.charCodeAt(i);

                sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: key,
                });
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            await fetch(@json(route('api.push.subscribe')), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    endpoint: sub.endpoint,
                    keys: { p256dh: sub.toJSON().keys?.p256dh, auth: sub.toJSON().keys?.auth },
                    contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
                }),
            });
        } catch (e) {
            console.warn('Push subscription failed:', e);
        }
    }

    pushBtn?.addEventListener('click', requestPushPermission);

    pushToggle?.addEventListener('change', () => {
        if (pushToggle.checked && Notification.permission !== 'granted') {
            pushBanner?.classList.remove('hidden');
            setPushStatus('Aktifkan izin notifikasi browser untuk push notification', 'warn');
        } else {
            checkPushState();
        }
    });

    checkPushState();

    // Tampilkan banner izin otomatis jika push aktif tapi belum diizinkan
    if (pushToggle?.checked && Notification.permission === 'default') {
        pushBanner?.classList.remove('hidden');
    }

    function checkboxOn(name) {
        return [...document.querySelectorAll('#adzan-form input[type="checkbox"][name="' + name + '"]')].some((el) => el.checked);
    }

    function selectedSoundType() {
        return document.querySelector('#adzan-form input[name="sound_type"]:checked')?.value || 'simple';
    }

    function collectFormSettings() {
        const form = document.getElementById('adzan-form');
        const formData = new FormData(form);
        return {
            location_id: formData.get('location_id') ? parseInt(formData.get('location_id'), 10) : null,
            imsak_enabled: checkboxOn('imsak_enabled'),
            subuh_enabled: checkboxOn('subuh_enabled'),
            dzuhur_enabled: checkboxOn('dzuhur_enabled'),
            ashar_enabled: checkboxOn('ashar_enabled'),
            maghrib_enabled: checkboxOn('maghrib_enabled'),
            isya_enabled: checkboxOn('isya_enabled'),
            sound_enabled: checkboxOn('sound_enabled'),
            sound_type: selectedSoundType(),
            volume: parseInt(formData.get('volume'), 10),
            push_enabled: checkboxOn('push_enabled'),
            minutes_before: parseInt(formData.get('minutes_before'), 10) || 0,
        };
    }

    const form = document.getElementById('adzan-form');
    form?.addEventListener('submit', async (e) => {
        // Pastikan lokasi terbaru dari Jadwal Sholat ikut tersimpan
        await syncLocationFromJadwal();
        const settings = collectFormSettings();

        if (settings.push_enabled && Notification.permission !== 'granted') {
            e.preventDefault();
            const granted = await requestPushPermission();
            if (!granted) {
                setPushStatus('Simpan dibatalkan — izin notifikasi diperlukan untuk push', 'warn');
                return;
            }
            if (window.AdzanClient) AdzanClient.updateSettingsFromForm(settings);
            form.submit();
            return;
        }

        if (window.AdzanClient) {
            AdzanClient.updateSettingsFromForm(settings);
        }
    });

    // Update info sholat berikutnya tiap menit
    setInterval(() => updateNextPrayerInfo(), 60000);

    updateNextPrayerInfo();
})();
</script>
<script>
(function () {
    const previewBtn = document.getElementById('preview-sound');
    const slider = document.getElementById('volume-slider');
    if (!previewBtn) return;

    let previewAudio = null;
    let previewCtx = null;

    previewBtn.addEventListener('click', function () {
        const type = document.querySelector('input[name="sound_type"]:checked')?.value || 'simple';
        const volume = parseInt(slider?.value || '80', 10);

        if (window.AdzanClient?.previewSound) {
            AdzanClient.previewSound(type, volume);
            return;
        }

        if (previewAudio) {
            previewAudio.pause();
            previewAudio = null;
        }

        if (type === 'simple') {
            try {
                if (previewCtx && previewCtx.state !== 'closed') previewCtx.close();
                previewCtx = new (window.AudioContext || window.webkitAudioContext)();
                previewCtx.resume();
                const gain = previewCtx.createGain();
                gain.gain.value = volume / 100;
                gain.connect(previewCtx.destination);
                const notes = [523.25, 659.25, 783.99, 659.25, 523.25];
                let time = previewCtx.currentTime;
                notes.forEach((freq) => {
                    const osc = previewCtx.createOscillator();
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    osc.connect(gain);
                    osc.start(time);
                    osc.stop(time + 0.32);
                    time += 0.4;
                });
            } catch (e) {
                console.warn('[Adzan Preview] tone failed', e);
            }
            return;
        }

        previewAudio = new Audio('/audio/adzan-' + type + '.mp3');
        previewAudio.volume = volume / 100;
        previewAudio.play().catch((err) => {
            console.warn('[Adzan Preview] mp3 failed', err);
            if (typeof showToast === 'function') {
                showToast('Gagal memutar audio. Cek file /audio/adzan-' + type + '.mp3', 'warning');
            }
        });
    });
})();
</script>
@endpush
