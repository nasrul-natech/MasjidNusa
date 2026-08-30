{{-- Adzan Client Timer: runs on all public pages, triggers sound when prayer time arrives --}}
<div id="adzan-banner" class="fixed top-0 left-0 right-0 z-[1000] transform -translate-y-full transition-transform duration-500" style="display:none">
    <div class="bg-emerald-800 text-white px-4 py-3 shadow-2xl safe-top">
        <div class="max-w-screen-xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🕌</span>
                <div>
                    <p class="font-bold text-sm" id="adzan-banner-title">Waktu Sholat</p>
                    <p class="text-xs text-emerald-200" id="adzan-banner-body">Telah masuk waktu sholat</p>
                </div>
            </div>
            <button id="adzan-banner-close" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 transition-colors text-sm font-bold">✕</button>
        </div>
    </div>
</div>

<script>
window.AdzanClient = (function () {
    const AUDIO_BASE = @json(asset('audio')) + '/';
    const STORAGE_KEY = 'adzan_settings';
    const SCHEDULE_URL = @json(route('adzan.schedule.json'));
    const IS_LOGGED_IN = @json(auth()->check());
    const PRAYER_NAMES = {
        imsak: 'Imsak', subuh: 'Subuh', dzuhur: 'Dzuhur',
        ashar: 'Ashar', maghrib: 'Maghrib', isya: 'Isya',
    };

    let timers = [];
    let audioCtx = null;
    let audioElement = null;
    let settings = null;
    let bannerTimeout = null;
    let currentSchedule = null;

    function getSettings() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch { return null; }
    }

    function saveSettings(s) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(s)); } catch {}
    }

    function defaultSettings() {
        return {
            imsak_enabled: false,
            subuh_enabled: true, dzuhur_enabled: true, ashar_enabled: true,
            maghrib_enabled: true, isya_enabled: true,
            sound_enabled: true, sound_type: 'simple', volume: 80,
            push_enabled: false, minutes_before: 0, location_id: null,
        };
    }

    function playSimpleTone(volume) {
        try {
            if (audioCtx) audioCtx.close();
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const gain = audioCtx.createGain();
            gain.gain.value = (volume || 80) / 100;
            gain.connect(audioCtx.destination);

            const melody = [
                [523.25, 0.5], [587.33, 0.3], [659.25, 0.5], [698.46, 0.3],
                [783.99, 0.8], [659.25, 0.5], [523.25, 0.8],
            ];
            let time = audioCtx.currentTime + 0.1;
            melody.forEach(([freq, dur]) => {
                const osc = audioCtx.createOscillator();
                osc.type = 'sine';
                osc.frequency.value = freq;
                osc.connect(gain);
                osc.start(time);
                osc.stop(time + dur * 0.85);
                time += dur;
            });

            const totalDur = melody.reduce((sum, [, d]) => sum + d, 0);
            setTimeout(() => { if (audioCtx) audioCtx.close(); audioCtx = null; }, (totalDur + 1) * 1000);
        } catch {}
    }

    function playAdzanAudio(type, volume) {
        try {
            if (audioElement) audioElement.pause();
            audioElement = new Audio(AUDIO_BASE + 'adzan-' + type + '.mp3');
            audioElement.volume = (volume || 80) / 100;
            audioElement.play().catch(() => playSimpleTone(volume));
        } catch { playSimpleTone(volume); }
    }

    function showBanner(prayerKey, time, isReminder) {
        const banner = document.getElementById('adzan-banner');
        if (!banner) return;

        const title = document.getElementById('adzan-banner-title');
        const body = document.getElementById('adzan-banner-body');
        const name = PRAYER_NAMES[prayerKey] || prayerKey;

        if (isReminder) {
            title.textContent = '⏰ Reminder ' + name;
            body.textContent = 'Akan masuk waktu ' + name + ' pukul ' + time;
        } else {
            title.textContent = '🕌 Waktu ' + name;
            body.textContent = 'Telah masuk waktu ' + name + ' (' + time + ')';
        }

        banner.style.display = '';
        requestAnimationFrame(() => banner.classList.remove('-translate-y-full'));

        clearTimeout(bannerTimeout);
        bannerTimeout = setTimeout(hideBanner, 30000);
    }

    function hideBanner() {
        const banner = document.getElementById('adzan-banner');
        if (!banner) return;
        banner.classList.add('-translate-y-full');
        setTimeout(() => { banner.style.display = 'none'; }, 500);
        stopAudio();
    }

    function stopAudio() {
        if (audioElement) { audioElement.pause(); audioElement = null; }
        if (audioCtx) { audioCtx.close(); audioCtx = null; }
    }

    function triggerAdzan(prayerKey, time, isReminder) {
        const s = settings || getSettings() || defaultSettings();

        showBanner(prayerKey, time, isReminder);

        if (s.sound_enabled && !isReminder) {
            if (s.sound_type === 'simple') {
                playSimpleTone(s.volume);
            } else {
                playAdzanAudio(s.sound_type, s.volume);
            }
        } else if (s.sound_enabled && isReminder) {
            playSimpleTone(Math.min(s.volume, 60));
        }
    }

    function buildScheduleUrl() {
        const prayerLocId = localStorage.getItem('prayer_location_id');
        const s = settings || getSettings() || defaultSettings();
        const params = new URLSearchParams();
        const locId = prayerLocId || s.location_id;

        if (locId) {
            params.set('location_id', locId);
        }

        const qs = params.toString();
        return SCHEDULE_URL + (qs ? '?' + qs : '');
    }

    function scheduleTimers(schedule) {
        timers.forEach(t => clearTimeout(t));
        timers = [];
        currentSchedule = schedule;

        const now = new Date();
        const s = settings || getSettings() || defaultSettings();
        const minutesBefore = parseInt(s.minutes_before, 10) || 0;

        Object.entries(schedule).forEach(([key, timeStr]) => {
            if (!PRAYER_NAMES[key]) return;
            if (!s[key + '_enabled']) return;
            if (!timeStr || timeStr === '--:--') return;

            const [h, m] = timeStr.split(':').map(Number);
            if (Number.isNaN(h) || Number.isNaN(m)) return;

            const target = new Date();
            target.setHours(h, m, 0, 0);

            const diff = target - now;
            if (diff <= 0 || diff > 86400000) return;

            // Timer tepat waktu sholat
            timers.push(setTimeout(() => triggerAdzan(key, timeStr, false), diff));

            // Timer reminder sebelum waktu sholat
            if (minutesBefore > 0) {
                const reminderDiff = diff - (minutesBefore * 60000);
                if (reminderDiff > 0) {
                    timers.push(setTimeout(() => triggerAdzan(key, timeStr, true), reminderDiff));
                }
            }
        });
    }

    async function fetchAndSchedule() {
        try {
            const resp = await fetch(buildScheduleUrl());
            const data = await resp.json();
            if (data.schedule) {
                if (data.location_id && settings) {
                    settings.location_id = data.location_id;
                    saveSettings(settings);
                }
                scheduleTimers(data.schedule);
                return data;
            }
        } catch {}
        return null;
    }

    function applyServerSettings(serverSettings) {
        settings = { ...(getSettings() || defaultSettings()), ...serverSettings };
        saveSettings(settings);
    }

    async function init() {
        settings = getSettings();
        if (!settings) {
            settings = defaultSettings();
            saveSettings(settings);
        }

        const closeBtn = document.getElementById('adzan-banner-close');
        closeBtn?.addEventListener('click', hideBanner);

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data?.type === 'ADZAN_TRIGGER') {
                    triggerAdzan(event.data.prayer, event.data.time, false);
                }
            });
        }

        await fetchAndSchedule();

        // Refresh timers setiap jam untuk antisipasi perubahan hari
        setInterval(() => fetchAndSchedule(), 3600000);
    }

    return {
        init,
        reload: fetchAndSchedule,
        triggerAdzan,
        stopAudio,
        hideBanner,
        getSettings,
        saveSettings,
        applyServerSettings,
        getCurrentSchedule: () => currentSchedule,
        updateSettingsFromForm: function (formSettings) {
            settings = { ...(settings || getSettings() || defaultSettings()), ...formSettings };
            saveSettings(settings);
            return fetchAndSchedule();
        },
    };
})();

document.addEventListener('DOMContentLoaded', () => AdzanClient.init());
</script>
