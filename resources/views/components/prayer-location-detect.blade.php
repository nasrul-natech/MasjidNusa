@props([
    'locations',
])

@php
    $locationOptionsJson = $locations->map(fn ($loc) => [
        'id'       => $loc->id,
        'city'     => $loc->city_name,
        'province' => $loc->province_name,
    ])->values();
@endphp

<script>
window.PrayerLocationDetect = (function () {
    const STORAGE_KEY       = 'prayer_location_id';
    const STORAGE_CITY      = 'prayer_location_city';
    const STORAGE_MANUAL    = 'prayer_location_manual';
    const SESSION_MANUAL    = 'prayer_picking_manual';
    const nearestUrl        = @json(route('prayer.nearest.json'));
    const todayJsonUrl      = @json(route('prayer.today.json'));

    const locationOptions = @json($locationOptionsJson);

    function saveLocation(locationId, cityName) {
        localStorage.setItem(STORAGE_KEY, locationId ?? '');
        localStorage.setItem(STORAGE_CITY, cityName ?? '');
    }

    function setManualChoice(locationId, cityName) {
        saveLocation(locationId, cityName);
        localStorage.setItem(STORAGE_MANUAL, '1');
    }

    function clearManualChoice() {
        localStorage.removeItem(STORAGE_MANUAL);
    }

    function isManualChoice() {
        return localStorage.getItem(STORAGE_MANUAL) === '1';
    }

    function shouldAutoDetect() {
        return !isManualChoice();
    }

    function markManualPickPending() {
        sessionStorage.setItem(SESSION_MANUAL, '1');
    }

    function consumeManualPickPending() {
        const pending = sessionStorage.getItem(SESSION_MANUAL) === '1';
        if (pending) sessionStorage.removeItem(SESSION_MANUAL);
        return pending;
    }

    function getSavedLocation() {
        return {
            id:   localStorage.getItem(STORAGE_KEY),
            city: localStorage.getItem(STORAGE_CITY),
        };
    }

    async function findNearestByCoords(lat, lon) {
        const res = await fetch(`${nearestUrl}?lat=${lat}&lon=${lon}`);
        if (!res.ok) throw new Error('nearest lookup failed');
        const data = await res.json();
        if (data.location_id) return data;

        return matchByNominatim(lat, lon);
    }

    async function matchByNominatim(lat, lon) {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&accept-language=id&zoom=10`,
            { headers: { 'User-Agent': 'MasjidNusa-App/1.0' } }
        );
        if (!res.ok) throw new Error('nominatim failed');

        const data = await res.json();
        const addr = data.address || {};
        const rawNames = [
            addr.city, addr.town, addr.municipality, addr.county, addr.suburb,
        ].filter(Boolean).map(n => n.toLowerCase());
        const rawProv = (addr.state || addr.region || '').toLowerCase();

        let best = null;
        let bestScore = 0;

        locationOptions.forEach(opt => {
            const city = (opt.city || '').toLowerCase();
            const prov = (opt.province || '').toLowerCase();
            let score = 0;

            rawNames.forEach(raw => {
                const normalized = raw.replace(/^(kota|kabupaten|kab\.|kec\.|kecamatan)\s+/i, '');
                if (normalized === city || city === normalized) score = Math.max(score, 100);
                else if (normalized.includes(city) || city.includes(normalized)) score = Math.max(score, 85);
            });

            if (rawProv && (rawProv.includes(prov) || prov.includes(rawProv))) score += 15;

            if (score > bestScore) {
                bestScore = score;
                best = opt;
            }
        });

        if (!best || bestScore < 30) return null;

        return {
            location_id:   best.id,
            city_name:     best.city,
            location_name: best.city,
        };
    }

    function requestGeolocation(onStatus) {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Browser tidak mendukung GPS'));
                return;
            }

            onStatus?.('Meminta izin lokasi…');

            navigator.geolocation.getCurrentPosition(
                async pos => {
                    try {
                        onStatus?.('Mencari kota terdekat…');
                        const { latitude: lat, longitude: lon } = pos.coords;
                        const location = await findNearestByCoords(lat, lon);
                        if (!location?.location_id) {
                            reject(new Error('Kota tidak ditemukan'));
                            return;
                        }
                        resolve(location);
                    } catch (e) {
                        reject(new Error('Gagal mendeteksi lokasi'));
                    }
                },
                err => {
                    const msg = err.code === 1
                        ? 'Izin lokasi ditolak'
                        : 'Gagal mendapatkan lokasi';
                    reject(new Error(msg));
                },
                { timeout: 12000, enableHighAccuracy: true, maximumAge: 60000 }
            );
        });
    }

    function fetchTodaySchedule(locationId, cityName, fromGps = false) {
        const url = todayJsonUrl + (locationId ? '?location_id=' + locationId : '');
        return fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.schedule) {
                    if (fromGps) {
                        saveLocation(locationId, cityName || data.location_name || '');
                        clearManualChoice();
                    }
                }
                return data;
            });
    }

    function autoDetect(onStatus) {
        return requestGeolocation(onStatus)
            .then(location => fetchTodaySchedule(location.location_id, location.city_name, true)
                .then(data => ({ location, schedule: data })));
    }

    return {
        STORAGE_KEY,
        STORAGE_CITY,
        saveLocation,
        setManualChoice,
        clearManualChoice,
        isManualChoice,
        shouldAutoDetect,
        markManualPickPending,
        consumeManualPickPending,
        getSavedLocation,
        requestGeolocation,
        fetchTodaySchedule,
        autoDetect,
    };
})();
</script>
