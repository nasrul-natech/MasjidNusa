@extends('layouts.public')

@section('title', 'Beranda')
@section('meta_description', 'Informasi dan Donasi ' . ($brand->mosqueName() ?? 'Masjid'))

@section('content')

{{-- Hero Banner Slider - Mobile --}}
@if($mobileBanners->count())
<div class="md:hidden relative w-full overflow-hidden bg-emerald-900"
    x-data="heroSlider({{ $mobileBanners->count() }}, 'mobile')"
    x-init="start()"
    @touchstart.passive="onTouchStart($event)" @touchend.passive="onTouchEnd($event)">
    <div class="relative w-full" style="padding-bottom:75%">
        @foreach($mobileBanners->values() as $i => $banner)
        <div class="absolute inset-0 w-full h-full transition-all duration-700 ease-in-out"
            :class="slideClass({{ $i }})">
            @if($banner->link)<a href="{{ $banner->link }}" class="block w-full h-full">@endif
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                    class="w-full h-full object-cover"
                    loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
            @if($banner->link)</a>@endif
            <div class="absolute bottom-5 left-4 right-4">
                <h2 class="text-white text-lg font-bold drop-shadow-lg line-clamp-2">{{ $banner->title }}</h2>
            </div>
        </div>
        @endforeach
    </div>
    @if($mobileBanners->count() > 1)
    <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 flex items-center gap-2 z-10">
        @foreach($mobileBanners->values() as $i => $b)
        <button @click="goTo({{ $i }})" class="relative h-2 rounded-full transition-all duration-300 overflow-hidden"
            :class="current === {{ $i }} ? 'w-7 bg-white/40' : 'w-2 bg-white/50'">
            <span x-show="current === {{ $i }}" class="absolute inset-y-0 left-0 bg-white rounded-full"
                :style="'width:' + progress + '%'" style="transition:width 100ms linear;"></span>
        </button>
        @endforeach
    </div>
    @endif
</div>
@endif

{{-- Hero Banner Slider - Desktop --}}
@if($desktopBanners->count())
<div class="hidden md:block bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 pt-4 lg:pt-6 pb-1 lg:pb-2">
        <div class="relative w-full overflow-hidden bg-emerald-900 rounded-2xl shadow-lg"
            x-data="heroSlider({{ $desktopBanners->count() }}, 'desktop')"
            x-init="start()"
            @mouseenter="pause()" @mouseleave="resume()">
            <div class="relative w-full" style="padding-bottom:32%">
        @foreach($desktopBanners->values() as $i => $banner)
        <div class="absolute inset-0 w-full h-full transition-all duration-700 ease-in-out"
            :class="slideClass({{ $i }})">
            @if($banner->link)<a href="{{ $banner->link }}" class="block w-full h-full">@endif
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}"
                    class="w-full h-full object-cover"
                    loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                    fetchpriority="{{ $i === 0 ? 'high' : 'auto' }}">
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
            @if($banner->link)</a>@endif
            <div class="absolute bottom-6 left-6 right-6">
                <h2 class="text-white text-xl lg:text-2xl font-bold drop-shadow-lg line-clamp-2">{{ $banner->title }}</h2>
            </div>
        </div>
        @endforeach
            </div>
            @if($desktopBanners->count() > 1)
            <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all z-10 opacity-70 hover:opacity-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/30 hover:bg-black/50 backdrop-blur-sm rounded-full flex items-center justify-center text-white transition-all z-10 opacity-70 hover:opacity-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-2 z-10">
                @foreach($desktopBanners->values() as $i => $b)
                <button @click="goTo({{ $i }})" class="relative h-2 rounded-full transition-all duration-300 overflow-hidden"
                    :class="current === {{ $i }} ? 'w-8 bg-white/40' : 'w-2 bg-white/50 hover:bg-white/70'">
                    <span x-show="current === {{ $i }}" class="absolute inset-y-0 left-0 bg-white rounded-full"
                        :style="'width:' + progress + '%'" style="transition:width 100ms linear;"></span>
                </button>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Fallback if no banners for current viewport --}}
@if($desktopBanners->isEmpty() && $mobileBanners->isEmpty())
<div class="bg-gradient-to-br from-emerald-800 via-emerald-700 to-emerald-600 px-4 py-14 text-center text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-4 left-4 w-32 h-32 border border-white/20 rounded-full"></div>
        <div class="absolute bottom-4 right-4 w-48 h-48 border border-white/20 rounded-full"></div>
    </div>
    <div class="relative z-10">
        <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->appName() }}" class="h-20 w-auto mx-auto mb-4 drop-shadow-lg">
        <h1 class="text-2xl sm:text-3xl font-bold">{{ $brand->mosqueName() }}</h1>
        <p class="text-emerald-200 mt-2 text-sm sm:text-base">Informasi & Donasi Digital</p>
    </div>
</div>
@endif

<script>
function heroSlider(total, mode) {
    return {
        current: 0, previous: -1, direction: 1, total: total,
        interval: null, progress: 0, progressInterval: null,
        duration: 6000, touchStartX: 0,

        start() { if (this.total > 1) this.runProgress(); },

        runProgress() {
            this.progress = 0;
            clearInterval(this.progressInterval);
            clearTimeout(this.interval);
            const step = 100 / (this.duration / 50);
            this.progressInterval = setInterval(() => {
                this.progress = Math.min(100, this.progress + step);
            }, 50);
            this.interval = setTimeout(() => this.next(), this.duration);
        },

        next() { this.direction = 1; this.previous = this.current; this.current = (this.current + 1) % this.total; this.runProgress(); },
        prev() { this.direction = -1; this.previous = this.current; this.current = (this.current - 1 + this.total) % this.total; this.runProgress(); },
        goTo(i) { if (i === this.current) return; this.direction = i > this.current ? 1 : -1; this.previous = this.current; this.current = i; this.runProgress(); },
        pause() { clearInterval(this.progressInterval); clearTimeout(this.interval); },
        resume() {
            if (this.total <= 1) return;
            const rem = this.duration * ((100 - this.progress) / 100);
            const step = 100 / (this.duration / 50);
            this.progressInterval = setInterval(() => { this.progress = Math.min(100, this.progress + step); }, 50);
            this.interval = setTimeout(() => this.next(), rem);
        },
        onTouchStart(e) { this.pause(); this.touchStartX = e.changedTouches[0].screenX; },
        onTouchEnd(e) {
            const d = e.changedTouches[0].screenX - this.touchStartX;
            Math.abs(d) > 50 ? (d < 0 ? this.next() : this.prev()) : this.resume();
        },
        slideClass(i) {
            if (i === this.current) return 'z-[2] opacity-100 translate-x-0';
            if (i === this.previous) return 'z-[1] opacity-0 ' + (this.direction > 0 ? '-translate-x-full' : 'translate-x-full');
            return 'z-0 opacity-0';
        }
    };
}
</script>

{{-- Jadwal Sholat Hari Ini --}}
@php
    $prayers5 = $todayTimes ? [
        ['name' => 'Subuh',   'time' => substr($todayTimes->subuh,   0, 5)],
        ['name' => 'Dzuhur',  'time' => substr($todayTimes->dzuhur,  0, 5)],
        ['name' => 'Ashar',   'time' => substr($todayTimes->ashar,   0, 5)],
        ['name' => 'Maghrib', 'time' => substr($todayTimes->maghrib, 0, 5)],
        ['name' => 'Isya',    'time' => substr($todayTimes->isya,    0, 5)],
    ] : null;

    $nextPrayerIndex = null;
    if ($prayers5) {
        $nowTime = now()->format('H:i');
        foreach ($prayers5 as $i => $p) {
            if ($p['time'] > $nowTime) {
                $nextPrayerIndex = $i;
                break;
            }
        }
        if ($nextPrayerIndex === null) {
            $nextPrayerIndex = 0;
        }
    } elseif ($prayerSchedules->count()) {
        $fallbackPrayers = $prayerSchedules->take(5);
        $nowTime = now()->format('H:i');
        foreach ($fallbackPrayers as $i => $prayer) {
            if (\Carbon\Carbon::parse($prayer->time)->format('H:i') > $nowTime) {
                $nextPrayerIndex = $i;
                break;
            }
        }
        if ($nextPrayerIndex === null) {
            $nextPrayerIndex = 0;
        }
    }
@endphp

@php
    $hasMobileBanner = $mobileBanners->count() > 0;
    $hasDesktopBanner = $desktopBanners->count() > 0;
@endphp

<div class="relative z-10 {{ $hasMobileBanner ? '-mt-3 rounded-t-[12px] overflow-hidden' : '' }} md:bg-transparent md:mt-0 md:rounded-none md:overflow-visible">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 {{ $hasDesktopBanner ? 'md:mt-4 lg:mt-5' : 'md:pt-4' }}">
        <div class="bg-emerald-700 text-white py-4 px-4 {{ $hasMobileBanner ? 'rounded-t-[12px]' : '' }} md:rounded-2xl md:shadow-xl md:px-6 md:py-5 lg:px-8 lg:py-6">
            {{-- Header row --}}
            <div class="flex items-start justify-between mb-3 lg:mb-4">
                <div>
                    <p class="text-xs lg:text-sm text-emerald-300">Jadwal Sholat</p>
                    <p class="font-semibold text-sm lg:text-base">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
                    <p class="text-[11px] lg:text-xs text-emerald-300 mt-0.5">{{ \App\Helpers\HijriDate::format(now()->year, now()->month, now()->day) }}</p>
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                    {{-- Location picker button --}}
                    @if($locations->count())
                    <button id="open-location-popup"
                        class="flex items-center gap-1 text-xs lg:text-sm text-emerald-300 hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span id="location-label">{{ $defaultLocation?->city_name ?? 'Pilih Lokasi' }}</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    @endif
                    <div class="flex items-center gap-2">
                        @auth
                        <button type="button" onclick="AdzanClient?.testSound()"
                                class="w-7 h-7 lg:w-8 lg:h-8 bg-white/15 rounded-lg flex items-center justify-center hover:bg-white/25 transition-colors"
                                title="Tes Suara Adzan">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m-6-6a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
                        </button>
                        <a href="{{ route('user.adzan.settings') }}" class="w-7 h-7 lg:w-8 lg:h-8 bg-white/15 rounded-lg flex items-center justify-center hover:bg-white/25 transition-colors" title="Pengaturan Adzan">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </a>
                        @endauth
                        <a href="{{ route('jadwal-sholat') }}" class="text-xs lg:text-sm text-emerald-300 hover:text-white whitespace-nowrap">Lihat semua →</a>
                    </div>
                </div>
            </div>

            {{-- Prayer times grid --}}
            <div id="prayer-grid" class="grid grid-cols-5 gap-2 lg:gap-3">
                @if($prayers5)
                    @foreach($prayers5 as $i => $p)
                    @php $isNext = $i === $nextPrayerIndex; @endphp
                    <div class="text-center rounded-xl py-2.5 lg:py-3.5 px-1 transition-all {{ $isNext ? 'bg-gradient-to-br from-[#d39929] to-[#b8821f] shadow-lg shadow-[#d39929]/45' : 'bg-emerald-600/50 lg:hover:bg-emerald-600/70' }}">
                        <p class="text-[10px] lg:text-xs font-medium {{ $isNext ? 'text-white/90' : 'text-emerald-300' }}">{{ $p['name'] }}</p>
                        <p class="text-sm lg:text-lg font-bold">{{ $p['time'] }}</p>
                    </div>
                    @endforeach
                @else
                    @foreach($prayerSchedules->take(5) as $i => $prayer)
                    @php $isNext = $i === $nextPrayerIndex; @endphp
                    <div class="text-center rounded-xl py-2 px-1 lg:py-3.5 transition-all {{ $isNext ? 'bg-gradient-to-br from-[#d39929] to-[#b8821f] shadow-lg shadow-[#d39929]/45' : 'bg-emerald-600/50' }}">
                        <p class="text-[10px] lg:text-xs font-medium {{ $isNext ? 'text-white/90' : 'text-emerald-300' }}">{{ $prayer->prayer_name }}</p>
                        <p class="text-sm lg:text-lg font-bold">{{ \Carbon\Carbon::parse($prayer->time)->format('H:i') }}</p>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Location Picker Popup --}}
@if($locations->count())
<x-prayer-location-detect :locations="$locations" />

<div id="location-popup" class="hidden fixed inset-0 z-50 flex items-end justify-center">
    {{-- Backdrop --}}
    <div id="popup-backdrop" class="absolute inset-0 bg-black/50 transition-opacity"></div>

    {{-- Sheet --}}
    <div class="relative bg-white rounded-t-3xl w-full max-w-md px-5 pt-5 pb-8 shadow-2xl">
        {{-- Handle --}}
        <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-4"></div>

        <h3 class="font-bold text-gray-900 text-base mb-4">Pilih Lokasi Jadwal Sholat</h3>

        {{-- GPS button --}}
        <button id="detect-gps"
            class="w-full flex items-center gap-3 p-3.5 rounded-2xl border-2 border-emerald-500 text-emerald-700 font-semibold text-sm mb-4 hover:bg-emerald-50 active:bg-emerald-100 transition-colors">
            <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06z"/>
                </svg>
            </div>
            <div class="text-left">
                <p class="font-semibold text-sm">Gunakan Lokasi Saya</p>
                <p id="gps-status" class="text-xs text-gray-500 font-normal">Deteksi otomatis via GPS</p>
            </div>
        </button>

        {{-- Divider --}}
        <div class="flex items-center gap-3 mb-3">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-xs text-gray-400 font-medium">atau pilih manual</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        {{-- Manual dropdown --}}
        <select id="location-select"
            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 mb-4 bg-white">
            <option value="">-- Pilih Lokasi --</option>
            @foreach($locations as $loc)
            <option value="{{ $loc->id }}"
                data-city="{{ $loc->city_name }}"
                {{ $defaultLocation?->id === $loc->id ? 'selected' : '' }}>
                {{ $loc->city_name }}, {{ $loc->province_name }}
            </option>
            @endforeach
        </select>

        {{-- Apply button --}}
        <button id="apply-location"
            class="w-full bg-emerald-700 text-white font-semibold py-3 rounded-2xl text-sm hover:bg-emerald-800 active:bg-emerald-900 transition-colors">
            Terapkan
        </button>
    </div>
</div>

<script>
(function () {
    const detect = window.PrayerLocationDetect;

    const popup         = document.getElementById('location-popup');
    const backdrop      = document.getElementById('popup-backdrop');
    const openBtn       = document.getElementById('open-location-popup');
    const locationLabel = document.getElementById('location-label');
    const locationSel   = document.getElementById('location-select');
    const applyBtn      = document.getElementById('apply-location');
    const detectGps     = document.getElementById('detect-gps');
    const gpsStatus     = document.getElementById('gps-status');
    const grid          = document.getElementById('prayer-grid');

    function openPopup()  { popup.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function closePopup() { popup.classList.add('hidden');    document.body.style.overflow = ''; }

    openBtn?.addEventListener('click', openPopup);
    backdrop?.addEventListener('click', closePopup);

    function getNextPrayerIndex(schedule) {
        const now = new Date();
        const nowStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        for (let i = 0; i < schedule.length; i++) {
            if (schedule[i].time > nowStr) return i;
        }
        return 0;
    }

    function renderGrid(schedule, cityName) {
        if (!schedule || !schedule.length) return;
        const nextIdx = getNextPrayerIndex(schedule);
        grid.innerHTML = schedule.map((p, i) => {
            const isNext = i === nextIdx;
            return `
            <div class="text-center rounded-xl py-2 px-1 lg:py-3.5 transition-all ${isNext ? 'bg-gradient-to-br from-[#d39929] to-[#b8821f] shadow-lg shadow-[#d39929]/45' : 'bg-emerald-600/50'}">
                <p class="text-[10px] lg:text-xs font-medium ${isNext ? 'text-white/90' : 'text-emerald-300'}">${p.name}</p>
                <p class="text-sm lg:text-lg font-bold">${p.time}</p>
            </div>`;
        }).join('');
        if (locationLabel && cityName) locationLabel.textContent = cityName;
    }

    function applySchedule(locationId, cityName, manual = false) {
        const fetch = manual
            ? detect.fetchTodaySchedule(locationId, cityName, false).then(data => {
                if (data.schedule) detect.setManualChoice(locationId, cityName || data.location_name || '');
                return data;
            })
            : detect.fetchTodaySchedule(locationId, cityName, false);

        fetch
            .then(data => {
                if (data.schedule) {
                    renderGrid(data.schedule, cityName || data.location_name);
                    if (locationSel) locationSel.value = locationId;
                }
            })
            .catch(() => {});
    }

    function runAutoDetect(statusEl) {
        return detect.autoDetect(msg => {
            if (statusEl) statusEl.textContent = msg;
        })
        .then(({ location, schedule }) => {
            if (schedule.schedule) {
                renderGrid(schedule.schedule, location.city_name || schedule.location_name);
            }
            if (locationSel) locationSel.value = location.location_id;
            if (statusEl) statusEl.textContent = 'Ditemukan: ' + (location.city_name || location.location_name);
        })
        .catch(err => {
            const saved = detect.getSavedLocation();
            if (saved.id) {
                applySchedule(saved.id, saved.city);
                if (locationLabel && saved.city) locationLabel.textContent = saved.city;
            }
            if (statusEl) statusEl.textContent = err.message || 'Gagal mendeteksi, pilih manual';
        });
    }

    // Auto-detect hanya saat buka pertama (belum pernah pilih manual)
    if (detect.shouldAutoDetect()) {
        runAutoDetect();
    } else {
        const saved = detect.getSavedLocation();
        if (saved.id) {
            applySchedule(saved.id, saved.city);
            if (locationLabel && saved.city) locationLabel.textContent = saved.city;
            if (locationSel) locationSel.value = saved.id;
        }
    }

    applyBtn?.addEventListener('click', () => {
        const opt = locationSel?.options[locationSel.selectedIndex];
        if (!opt?.value) return;
        applySchedule(opt.value, opt.dataset.city, true);
        closePopup();
    });

    detectGps?.addEventListener('click', () => {
        detect.clearManualChoice();
        detectGps.disabled = true;
        runAutoDetect(gpsStatus).finally(() => { detectGps.disabled = false; });
    });
})();
</script>
@endif

{{-- Quick Actions --}}
<div class="max-w-7xl mx-auto px-4 lg:px-6 py-5 lg:py-8">
    <div class="lg:bg-white lg:rounded-2xl lg:shadow-sm lg:border lg:border-gray-100 lg:p-6 lg:max-w-4xl lg:mx-auto">
        <h2 class="text-sm lg:text-base font-bold text-gray-900 mb-3 lg:mb-5 text-center lg:text-left">Menu Utama</h2>
        <div class="grid grid-cols-3 gap-2.5 sm:grid-cols-6 lg:grid-cols-6 lg:gap-4 lg:max-w-3xl lg:mx-auto">
        <x-quick-action href="{{ route('quran.index') }}" icon="quran" label="Al-Quran" color="indigo" :active="request()->routeIs('quran.*')" />
        <x-quick-action href="{{ route('doa.index') }}" icon="doa" label="Doa" color="teal" :active="request()->routeIs('doa.*')" />
        <x-quick-action href="{{ route('donasi.index') }}" icon="donation" label="Donasi" color="red" :active="request()->routeIs('donasi.*')" />
        <x-quick-action href="{{ route('kegiatan.index') }}" icon="calendar" label="Kegiatan" color="blue" :active="request()->routeIs('kegiatan.*')" />
        <x-quick-action href="{{ route('dakwah.index') }}" icon="book" label="Dakwah" color="amber" :active="request()->routeIs('dakwah.*')" />
        <x-quick-action href="{{ route('info-masjid.index') }}" icon="building" label="Info" color="emerald" :active="request()->routeIs('info-masjid.*')" />
        </div>
    </div>
</div>

{{-- Main Content: Desktop 2-column / Mobile stack --}}
<div class="max-w-7xl mx-auto px-4 lg:px-6 pb-8 lg:pb-10">
<div class="lg:grid lg:grid-cols-12 lg:gap-8 lg:items-start">

    {{-- Left: Activities + Articles --}}
    <div class="lg:col-span-8 space-y-8 lg:space-y-10">

        {{-- Upcoming Activities --}}
        @if($upcomingActivities->count())
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base lg:text-lg font-bold text-gray-900">Kegiatan Mendatang</h2>
                <a href="{{ route('kegiatan.index') }}" class="text-xs lg:text-sm text-emerald-600 font-medium hover:text-emerald-700">Lihat semua →</a>
            </div>
            {{-- Mobile: horizontal scroll --}}
            <div class="md:hidden overflow-x-auto -mx-4 px-4 scrollbar-hide">
                <div class="flex gap-3 pb-2" style="width: max-content">
                    @foreach($upcomingActivities as $activity)
                    <a href="{{ route('kegiatan.show', $activity->slug) }}" class="w-52 flex-shrink-0 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="h-28 bg-emerald-100 relative overflow-hidden">
                            @if($activity->banner)
                            <img src="{{ $activity->banner_url }}" alt="{{ $activity->title }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center">
                                @include('components.icons.calendar', ['class' => 'w-10 h-10 text-emerald-300'])
                            </div>
                            @endif
                            <div class="absolute top-2 left-2 bg-emerald-700 text-white text-[10px] font-bold px-2 py-1 rounded-lg">{{ $activity->start_date->format('d M') }}</div>
                        </div>
                        <div class="p-3">
                            <p class="text-xs font-bold text-gray-900 line-clamp-2">{{ $activity->title }}</p>
                            @if($activity->location)<p class="text-[10px] text-gray-500 mt-1">📍 {{ $activity->location }}</p>@endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            {{-- Desktop: 2-col grid in main column --}}
            <div class="hidden md:grid md:grid-cols-2 gap-4">
                @foreach($upcomingActivities as $activity)
                <a href="{{ route('kegiatan.show', $activity->slug) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
                    <div class="h-32 bg-emerald-100 relative overflow-hidden">
                        @if($activity->banner)
                        <img src="{{ $activity->banner_url }}" alt="{{ $activity->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            @include('components.icons.calendar', ['class' => 'w-10 h-10 text-emerald-300'])
                        </div>
                        @endif
                        <div class="absolute top-2.5 left-2.5 bg-emerald-700 text-white text-xs font-bold px-2 py-1 rounded-lg">{{ $activity->start_date->format('d M Y') }}</div>
                    </div>
                    <div class="p-3.5">
                        <p class="text-sm font-bold text-gray-900 line-clamp-2">{{ $activity->title }}</p>
                        @if($activity->location)<p class="text-xs text-gray-500 mt-1">📍 {{ Str::limit($activity->location, 40) }}</p>@endif
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Donations: mobile only (between activities & articles) --}}
        @if($activeCampaigns->count())
        <section class="lg:hidden">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900">Program Donasi Aktif</h2>
                <a href="{{ route('donasi.index') }}" class="text-xs text-emerald-600 font-medium hover:text-emerald-700">Lihat semua →</a>
            </div>
            <div class="space-y-3">
                @foreach($activeCampaigns as $campaign)
                <a href="{{ route('donasi.show', $campaign->slug) }}" class="flex gap-4 p-4 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-16 h-16 rounded-xl bg-emerald-100 flex-shrink-0 overflow-hidden">
                        @if($campaign->banner)
                        <img src="{{ $campaign->banner_url }}" alt="{{ $campaign->title }}" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            @include('components.icons.donation', ['class' => 'w-8 h-8 text-emerald-400'])
                        </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 line-clamp-1">{{ $campaign->title }}</p>
                        <div class="mt-2">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Terkumpul {{ $campaign->progress_percent }}%</span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $campaign->progress_percent }}%"></div>
                            </div>
                            <p class="text-xs font-semibold text-emerald-700 mt-1.5">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Latest Articles --}}
        @if($latestArticles->count())
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base lg:text-lg font-bold text-gray-900">Dakwah & Artikel</h2>
                <a href="{{ route('dakwah.index') }}" class="text-xs lg:text-sm text-emerald-600 font-medium hover:text-emerald-700">Lihat semua →</a>
            </div>
            {{-- Mobile: list style --}}
            <div class="md:hidden space-y-3">
                @foreach($latestArticles as $article)
                <a href="{{ route('dakwah.show', $article->slug) }}" class="flex gap-3 p-4 bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="w-14 h-14 rounded-xl bg-amber-50 flex-shrink-0 overflow-hidden">
                        @if($article->thumbnail)
                        <img src="{{ $article->thumbnail_url }}" class="w-full h-full object-cover" alt="{{ $article->title }}">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            @include('components.icons.book', ['class' => 'w-8 h-8 text-amber-400'])
                        </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 line-clamp-2">{{ $article->title }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $article->published_at->diffForHumans() }}</p>
                    </div>
                </a>
                @endforeach
            </div>
            {{-- Desktop: 2-col grid --}}
            <div class="hidden md:grid md:grid-cols-2 gap-4">
                @foreach($latestArticles as $article)
                <a href="{{ route('dakwah.show', $article->slug) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">
                    <div class="h-28 bg-amber-50 relative overflow-hidden">
                        @if($article->thumbnail)
                        <img src="{{ $article->thumbnail_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="{{ $article->title }}">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            @include('components.icons.book', ['class' => 'w-10 h-10 text-amber-400'])
                        </div>
                        @endif
                    </div>
                    <div class="p-3.5">
                        <p class="text-sm font-semibold text-gray-900 line-clamp-2">{{ $article->title }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $article->published_at->diffForHumans() }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>

    {{-- Right: Donations sidebar (desktop only) --}}
    @if($activeCampaigns->count())
    <aside class="hidden lg:block lg:col-span-4 lg:sticky lg:top-20">
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900">Program Donasi</h2>
                <a href="{{ route('donasi.index') }}" class="text-xs text-emerald-600 font-medium hover:text-emerald-700">Semua →</a>
            </div>
            <div class="space-y-4">
                @foreach($activeCampaigns as $campaign)
                <a href="{{ route('donasi.show', $campaign->slug) }}" class="block p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-emerald-50 hover:border-emerald-200 transition-colors">
                    <div class="flex gap-3">
                        <div class="w-14 h-14 rounded-xl bg-emerald-100 flex-shrink-0 overflow-hidden">
                            @if($campaign->banner)
                            <img src="{{ $campaign->banner_url }}" alt="{{ $campaign->title }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center">
                                @include('components.icons.donation', ['class' => 'w-6 h-6 text-emerald-400'])
                            </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 line-clamp-2">{{ $campaign->title }}</p>
                            <p class="text-xs font-semibold text-emerald-700 mt-1">{{ $campaign->progress_percent }}% terkumpul</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $campaign->progress_percent }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
    </aside>
    @endif

</div>
</div>

@endsection
