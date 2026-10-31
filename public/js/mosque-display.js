(() => {
    'use strict';
    const prayerKeys = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];
    const displayPrayerOrder = ['imsak', 'subuh', 'terbit', 'dzuhur', 'ashar', 'maghrib', 'isya'];
    const bounded = (value, min, max, fallback) => Number.isInteger(value) && value >= min && value <= max ? value : fallback;
    function timestamp(value) {
        if (typeof value !== 'string' || !/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/.test(value)) return NaN;
        const year = Number(value.slice(0, 4)), month = Number(value.slice(5, 7)), day = Number(value.slice(8, 10));
        if (month < 1 || month > 12 || day < 1 || day > new Date(Date.UTC(year, month, 0)).getUTCDate() || Number(value.slice(11, 13)) > 23 || Number(value.slice(14, 16)) > 59 || Number(value.slice(17, 19)) > 59) return NaN;
        return Date.parse(value);
    }
    function prayerState(feed, now, verified) {
        const normal = {mode: 'normal', current: null, upcoming: null};
        if (!verified || !Number.isFinite(now)) return normal;
        const entries = [...(feed.prayers || []), feed.previous_prayer, feed.next_prayer].filter(prayer => prayer && prayerKeys.includes(prayer.key));
        if (entries.some(prayer => !Number.isFinite(timestamp(prayer.at)))) return normal;
        entries.sort((a, b) => timestamp(a.at) - timestamp(b.at));
        const current = entries.filter(prayer => timestamp(prayer.at) <= now).pop() || null;
        const upcoming = entries.find(prayer => timestamp(prayer.at) > now) || null;
        const state = {mode: 'normal', current, upcoming};
        if (feed.prayer_mode_enabled !== true || !current) return state;
        const adhanEnd = timestamp(current.at) + bounded(feed.adhan_minutes, 1, 15, 3) * 60000;
        const iqamahEnd = adhanEnd + bounded(feed.iqamah_minutes, 0, 30, 10) * 60000;
        const prayerEnd = iqamahEnd + bounded(feed.prayer_minutes, 1, 60, 15) * 60000;
        if (now < adhanEnd) return {...state, mode: 'adhan', remaining: Math.ceil((adhanEnd - now) / 1000)};
        if (now < iqamahEnd) return {...state, mode: 'iqamah', remaining: Math.ceil((iqamahEnd - now) / 1000)};
        if (now < prayerEnd) return {...state, mode: 'prayer', remaining: Math.ceil((prayerEnd - now) / 1000)};
        return state;
    }
    if (typeof module !== 'undefined' && module.exports) module.exports = {prayerState, timestamp};
    if (typeof document === 'undefined') return;
    const preview = document.body.dataset.preview === '1';
    const previewPhase = preview && ['adhan', 'iqamah', 'prayer'].includes(document.body.dataset.previewPhase) ? document.body.dataset.previewPhase : '';
    const demoState = () => ({mode: previewPhase, current: {name: 'Contoh'}, remaining: {adhan: 180, iqamah: 600, prayer: 900}[previewPhase]});
    const feedUrl = document.body.dataset.feed;
    const storageKey = 'mosque-display-public-v1';
    const photoCache = 'mosque-display-public-photos-v1';
    const byId = id => document.getElementById(id);
    const monotonic = () => typeof performance !== 'undefined' ? performance.now() : Date.now();
    let data = null, connected = false, delay = 30000, rotation = null, position = 0, objectUrl = null, lastSignature = '', generation = 0, pollTimer = null, polling = false, lastReceived = 0, mode = 'normal';
    let acceptedServer = null, acceptedAt = 0, cancelPending = null, confirmedSequence = null;

    function localDate(date, timezone) {
        return new Intl.DateTimeFormat('en-CA', {timeZone: timezone, year: 'numeric', month: '2-digit', day: '2-digit'}).format(date);
    }
    const offlineDetail = 'Offline / koneksi gagal. Konten publik terakhir; waktu perangkat tidak diverifikasi.';
    function setBadgeState(badge, state) {
        if (badge.dataset) badge.dataset.state = state;
        else if (typeof badge.setAttribute === 'function') badge.setAttribute('data-state', state);
    }
    function syncConnectionBadge() {
        const badge = byId('connection-badge'), connection = byId('connection');
        if (!badge || !connection) return;
        if (!data) {
            setBadgeState(badge, 'pending');
            connection.textContent = 'Menghubungkan...';
            if (typeof badge.removeAttribute === 'function') badge.removeAttribute('title');
            else badge.title = '';
            return;
        }
        if (connected) {
            setBadgeState(badge, 'online');
            connection.textContent = 'Terhubung';
            if (typeof badge.removeAttribute === 'function') badge.removeAttribute('title');
            else badge.title = '';
        } else {
            setBadgeState(badge, 'offline');
            connection.textContent = 'Offline';
            badge.title = offlineDetail;
        }
    }
    function status() {
        if (connected && monotonic() - lastReceived > 75000) { connected = false; clearMedia(); play(); }
        syncConnectionBadge();
        if (previewPhase) byId('clock').textContent = new Intl.DateTimeFormat('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false}).format(new Date());
        if (!data) { confirmedSequence = null; setMode(previewPhase ? demoState() : {mode: 'normal'}); return; }
        const now = new Date(connected ? acceptedServer + monotonic() - acceptedAt : Date.now());
        byId('clock').textContent = connected ? new Intl.DateTimeFormat('id-ID', {timeZone: data.timezone, hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false}).format(now) : '--:--:--';
        byId('date').textContent = connected ? new Intl.DateTimeFormat('id-ID', {timeZone: data.timezone, dateStyle: 'full'}).format(now) : '';
        const today = localDate(now, data.timezone);
        const fresh = connected && today === data.prayer_date;
        const candidate = prayerState(data, now.getTime(), connected);
        const dayGap = (Date.parse(`${today}T00:00:00Z`) - Date.parse(`${data.prayer_date}T00:00:00Z`)) / 86400000;
        // Bridge only an active, timestamp-covered adjacent-day session; never revive an old schedule.
        const currentDate = candidate.current ? localDate(new Date(timestamp(candidate.current.at)), data.timezone) : null;
        const bridged = connected && Math.abs(dayGap) === 1 && candidate.mode !== 'normal' && [data.prayer_date, today].includes(currentDate);
        let state = fresh || bridged ? candidate : {mode: 'normal'};
        if (connected) {
            confirmedSequence = state.mode !== 'normal' ? {...data, prayers: [state.current], previous_prayer: null, next_prayer: null} : null;
        } else if (confirmedSequence && acceptedServer !== null) {
            // Finish only the sequence verified in this tab; never start another from offline cache.
            state = prayerState(confirmedSequence, acceptedServer + monotonic() - acceptedAt, true);
            if (state.mode === 'normal') confirmedSequence = null;
        }
        if (previewPhase) {
            state = demoState();
            if (!connected) byId('clock').textContent = new Intl.DateTimeFormat('id-ID', {hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false}).format(new Date());
        }
        setMode(state);
        byId('prayers').replaceChildren();
        if (!fresh || !data.prayers.length) {
            byId('prayers').textContent = fresh ? 'Jadwal belum tersedia' : 'Jadwal tidak terverifikasi. Menunggu koneksi dan tanggal terbaru.';
        } else {
            const schedule = data.prayers
                .filter(prayer => displayPrayerOrder.includes(prayer.key))
                .sort((a, b) => displayPrayerOrder.indexOf(a.key) - displayPrayerOrder.indexOf(b.key));
            document.body.style.setProperty('--prayer-cols', String(Math.max(schedule.length, 1)));
            schedule.forEach(prayer => {
                const row = document.createElement('div'), name = document.createElement('span'), time = document.createElement('strong');
                const matches = candidate => candidate && candidate.key === prayer.key && candidate.at === prayer.at;
                const highlight = prayerKeys.includes(prayer.key);
                row.className = highlight && matches(state.current) ? 'current' : highlight && matches(state.upcoming) ? 'upcoming' : '';
                if (!prayerKeys.includes(prayer.key)) {
                    if (row.classList) row.classList.add('extra');
                    else row.className = `${row.className || ''} extra`.trim();
                }
                name.textContent = prayer.name; time.textContent = prayer.time; row.append(name, time); byId('prayers').append(row);
            });
        }
        byId('activities').hidden = !connected;
        document.body.dataset.activities = connected && data.activities.length ? '1' : '0';
    }
    function setMode(state) {
        const changed = mode !== state.mode;
        mode = state.mode;
        document.body.dataset.mode = mode;
        if (changed && mode !== 'normal') { clearTimeout(rotation); clearMedia(); }
        if (mode !== 'normal') {
            byId('mode-clock').textContent = byId('clock').textContent;
            byId('mode-title').textContent = mode === 'adhan' ? `Waktu Adzan ${state.current.name}` : mode === 'iqamah' ? `Menuju Iqamah ${state.current.name}` : '';
            byId('mode-note').textContent = previewPhase ? 'Simulasi pratinjau. Hitung mundur contoh; jadwal asli tidak berubah.' : mode === 'adhan' ? 'Adzan visual. Audio tidak dijamin.' : mode === 'iqamah' ? 'Luruskan dan rapatkan saf' : '';
            byId('mode-countdown').textContent = `${String(Math.floor(state.remaining / 60)).padStart(2, '0')}:${String(state.remaining % 60).padStart(2, '0')}`;
        }
        if (changed && mode === 'normal') play();
    }
    function clearMedia() {
        generation++;
        if (cancelPending) { cancelPending(); cancelPending = null; }
        byId('media').querySelectorAll('img,iframe,video,audio').forEach(node => { if (typeof node.pause === 'function') node.pause(); node.remove(); });
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        byId('fallback').hidden = false;
        byId('media-message').hidden = true;
    }
    function youtubeEmbedOriginParam() {
        try {
            const origin = new URL(location.href).origin;
            if (['https:', 'http:'].includes(new URL(origin).protocol)) return origin;
        } catch {}
        return '';
    }
    function youtubeEmbedSrc(videoId, host) {
        const query = ['autoplay=1', 'mute=1', 'playsinline=1', 'rel=0', 'controls=0', 'enablejsapi=1', 'iv_load_policy=3', 'modestbranding=1', 'fs=1'];
        const origin = youtubeEmbedOriginParam();
        if (origin) query.push(`origin=${encodeURIComponent(origin)}`);
        try { query.push(`widget_referrer=${encodeURIComponent(location.href.split('#')[0])}`); } catch {}
        return `https://${host}/embed/${videoId}?${query.join('&')}`;
    }
    const youtubeHosts = ['www.youtube-nocookie.com', 'www.youtube.com'];
    async function play() {
        clearTimeout(rotation);
        generation++;
        if (cancelPending) { cancelPending(); cancelPending = null; }
        if (!data || mode !== 'normal') return;
        // Dated media is filtered server-side; offline playback only uses last safe public photos.
        const networkMedia = connected || preview;
        const items = data.items.filter(item => networkMedia || (item.type === 'photo' && item.scheduled === false));
        if (!items.length) { clearMedia(); return; }
        const item = items[position++ % items.length];
        const currentGeneration = generation;
        if (item.type === 'photo') {
            const image = document.createElement('img');
            image.alt = item.title;
            let url = item.photo_url, pendingUrl = null, settled = false;
            const clean = () => { settled = true; clearTimeout(loadTimeout); image.onload = null; image.onerror = null; if (pendingUrl) URL.revokeObjectURL(pendingUrl); pendingUrl = null; };
            const fail = () => { if (settled) return; clean(); if (generation === currentGeneration) { cancelPending = null; rotation = setTimeout(play, 1000); } };
            const loadTimeout = setTimeout(fail, 15000);
            cancelPending = clean;
            if (!connected && !preview && 'caches' in window) {
                try {
                    const response = await (await caches.open(photoCache)).match(url);
                    if (!response) { fail(); return; }
                    const blob = await response.blob();
                    if (generation !== currentGeneration || settled) return;
                    pendingUrl = URL.createObjectURL(blob); url = pendingUrl;
                } catch { fail(); return; }
            }
            if (generation !== currentGeneration || settled) return;
            image.onload = () => {
                if (generation !== currentGeneration || settled) return;
                const loadedUrl = pendingUrl; pendingUrl = null;
                clean(); cancelPending = null;
                clearMedia(); objectUrl = loadedUrl;
                byId('media').append(image); byId('fallback').hidden = true;
                rotation = setTimeout(play, item.duration_seconds * 1000);
            };
            image.onerror = fail;
            image.src = url;
        } else if (networkMedia && item.type === 'youtube' && /^[A-Za-z0-9_-]{11}$/.test(String(item.youtube_id || ''))) {
            let hostIndex = 0;
            let frame = null;
            let receive = null;
            const message = byId('media-message');
            let mediaGeneration = generation;
            const fail = () => {
                if (generation !== mediaGeneration) return;
                if (frame) frame.remove();
                message.hidden = true;
                byId('fallback').textContent = 'Video YouTube tidak tersedia. Coba video lain atau periksa izin embed. Pengumuman dan jadwal tetap tersedia.';
                byId('fallback').hidden = false;
            };
            const postCommand = (func, args = []) => {
                if (!frame?.contentWindow) return;
                const target = `https://${youtubeHosts[hostIndex]}`;
                frame.contentWindow.postMessage(JSON.stringify({event: 'command', func, args}), target);
            };
            const mountYoutube = () => {
                if (frame) frame.remove();
                if (hostIndex === 0) clearMedia();
                mediaGeneration = generation;
                message.textContent = 'Memuat YouTube…';
                message.hidden = false;
                frame = document.createElement('iframe');
                frame.title = item.title;
                frame.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen';
                frame.allowFullscreen = true;
                frame.referrerPolicy = 'strict-origin-when-cross-origin';
                frame.loading = 'eager';
                const apiOrigin = `https://${youtubeHosts[hostIndex]}`;
                frame.src = youtubeEmbedSrc(item.youtube_id, youtubeHosts[hostIndex]);
                const boundWindow = frame.contentWindow;
                if (receive) window.removeEventListener('message', receive);
                receive = event => {
                    if (generation !== mediaGeneration || event.origin !== apiOrigin || event.source !== boundWindow) return;
                    let payload;
                    try { payload = typeof event.data === 'string' ? JSON.parse(event.data) : event.data; } catch { return; }
                    if (!payload || typeof payload !== 'object') return;
                    if (payload.event === 'onReady') {
                        postCommand('addEventListener', ['onError']);
                        postCommand('addEventListener', ['onStateChange']);
                        postCommand('playVideo');
                    }
                    if (payload.event === 'onError') {
                        if (hostIndex < youtubeHosts.length - 1) {
                            hostIndex++;
                            mountYoutube();
                            return;
                        }
                        fail();
                    }
                    if (payload.event === 'onStateChange' && payload.info === 1) {
                        message.hidden = true;
                        byId('fallback').hidden = true;
                    }
                };
                window.addEventListener('message', receive);
                cancelPending = () => {
                    window.removeEventListener('message', receive);
                    frame.onload = null;
                    frame.onerror = null;
                };
                frame.onload = () => {
                    if (frame.contentWindow) frame.contentWindow.postMessage(JSON.stringify({event: 'listening'}), apiOrigin);
                    postCommand('addEventListener', ['onReady']);
                    postCommand('addEventListener', ['onError']);
                    postCommand('addEventListener', ['onStateChange']);
                    postCommand('playVideo');
                };
                frame.onerror = () => {
                    if (hostIndex < youtubeHosts.length - 1) {
                        hostIndex++;
                        mountYoutube();
                        return;
                    }
                    fail();
                };
                byId('media').append(frame);
            };
            mountYoutube();
            rotation = setTimeout(play, item.duration_seconds * 1000);
        } else {
            rotation = setTimeout(play, 800);
        }
    }
    function render(next, restart = false) {
        const signature = JSON.stringify([next.version, next.items]);
        data = next;
        document.body.dataset.theme = ['emerald', 'daylight', 'midnight', 'sand', 'royal', 'ocean', 'maroon', 'olive', 'slate'].includes(data.theme) ? data.theme : 'emerald';
        document.body.dataset.decoration = ['none', 'mosque', 'clouds', 'ramadan', 'qurban', 'desert', 'stars'].includes(data.decoration) ? data.decoration : 'mosque';
        document.body.dataset.announcementSize = ['normal', 'large', 'extra'].includes(data.announcement_size) ? data.announcement_size : 'large';
        document.body.style.setProperty('--announcement-seconds', `${bounded(data.announcement_seconds, 10, 180, 40)}s`);
        document.body.style.setProperty('--transition-ms', `${bounded(data.transition_ms, 200, 2000, 800)}ms`);
        document.body.dataset.layout = ['focus', 'split', 'immersive'].includes(data.layout) ? data.layout : 'focus';
        document.body.dataset.mediaFit = data.media_fit === 'cover' ? 'cover' : 'contain';
        const logo = byId('logo');
        logo.src = typeof data.logo_url === 'string' && /^\/storage\/branding\/[A-Za-z0-9_-]+\.(?:png|jpe?g|webp|gif)$/.test(data.logo_url) ? data.logo_url : '/images/logo-masjidnusa.png';
        logo.onerror = () => { logo.onerror = null; logo.src = '/images/logo-masjidnusa.png'; };
        if (/^#[a-f0-9]{6}$/i.test(data.accent)) document.body.style.setProperty('--accent', data.accent);
        byId('title').textContent = data.title;
        byId('announcement').textContent = data.announcement || 'Selamat datang';
        byId('fallback').textContent = data.announcement || 'Selamat datang. Media belum tersedia.';
        byId('prayer-heading').textContent = 'Jadwal Salat';
        byId('prayer-location').textContent = data.prayer_location || '';
        byId('updated').textContent = data.published_at ? `Terbit: ${new Intl.DateTimeFormat('id-ID', {timeZone: data.timezone, dateStyle: 'short', timeStyle: 'short'}).format(new Date(data.published_at))}` : 'Draft';
        byId('activities').replaceChildren();
        data.activities.forEach(activity => {
            const line = document.createElement('p');
            line.textContent = `${activity.title} | ${new Intl.DateTimeFormat('id-ID', {timeZone: data.timezone, dateStyle: 'short', timeStyle: 'short'}).format(new Date(activity.starts_at))}`;
            byId('activities').append(line);
        });
        const previousMode = mode;
        status();
        if (signature !== lastSignature) { lastSignature = signature; position = 0; clearMedia(); play(); }
        else if (restart && previousMode === mode) play();
    }
    async function remember(next) {
        if (preview) return;
        // No admin feed, HTML, cookies, or user data enters this explicit offline cache.
        try { localStorage.setItem(storageKey, JSON.stringify(next)); } catch {}
        if (!('caches' in window)) return;
        try {
            const cache = await caches.open(photoCache);
            const urls = next.items.filter(item => item.type === 'photo').map(item => item.photo_url);
            for (const request of await cache.keys()) if (!urls.includes(request.url)) await cache.delete(request);
            for (const url of urls) {
                if (new URL(url).origin !== location.origin || !new URL(url).pathname.startsWith('/display-masjid/photos/')) continue;
                if (await cache.match(url)) continue;
                const response = await fetch(url, {credentials: 'omit', cache: 'no-store'});
                if (response.ok && response.headers.get('Content-Type')?.startsWith('image/jpeg')) await cache.put(url, response);
            }
        } catch {}
    }
    async function poll() {
        if (polling) return;
        polling = true;
        clearTimeout(pollTimer);
        const controller = new AbortController(), timeout = setTimeout(() => controller.abort(), 12000);
        try {
            const response = await fetch(feedUrl, {cache: 'no-store', credentials: preview ? 'same-origin' : 'omit', headers: {'Accept': 'application/json'}, signal: controller.signal});
            if (!response.ok) {
                if (!preview && [403, 404].includes(response.status)) {
                    data = null; lastSignature = ''; clearTimeout(rotation); clearMedia(); byId('fallback').textContent = 'Display belum diterbitkan atau dinonaktifkan.';
                    connected = false; setMode({mode: 'normal'});
                    byId('announcement').textContent = ''; byId('prayers').textContent = 'Jadwal belum tersedia'; byId('activities').replaceChildren();
                    byId('title').textContent = 'Display Masjid'; byId('clock').textContent = '--:--:--'; byId('date').textContent = ''; byId('updated').textContent = '';
                    byId('logo').src = '/images/logo-masjidnusa.png'; document.body.dataset.activities = '0';
                    try { localStorage.removeItem(storageKey); } catch {}
                    try { if ('caches' in window) caches.delete(photoCache).catch(() => {}); } catch {}
                }
                throw new Error('Feed unavailable');
            }
            const next = await response.json();
            const wasOffline = !connected;
            const serverTime = timestamp(next.server_now);
            if (!Number.isFinite(serverTime)) throw new Error('Invalid server timestamp');
            // Reject invalid timezone before replacing last safe public content.
            localDate(new Date(serverTime), next.timezone);
            const receivedAt = monotonic();
            if (acceptedServer !== null && serverTime < acceptedServer + Math.max(0, receivedAt - acceptedAt) - 5000) throw new Error('Regressed server timestamp');
            acceptedServer = serverTime; acceptedAt = receivedAt;
            connected = true; lastReceived = receivedAt; delay = 30000;
            render(next, wasOffline); remember(next);
        } catch {
            const wasConnected = connected; connected = false; if (wasConnected) clearMedia(); delay = Math.min(delay * 2, 300000); status();
            if (wasConnected) play();
        } finally { polling = false; clearTimeout(timeout); pollTimer = setTimeout(poll, delay); }
    }
    const fullscreen = byId('fullscreen');
    fullscreen.addEventListener('click', async () => {
        try {
            if (document.fullscreenElement) await document.exitFullscreen();
            else if (document.documentElement.requestFullscreen) await document.documentElement.requestFullscreen();
            else throw new Error('Fullscreen unavailable');
            byId('fullscreen-message').textContent = '';
        } catch { byId('fullscreen-message').textContent = 'Fullscreen tidak tersedia. Gunakan menu layar penuh browser TV.'; }
    });
    document.addEventListener('fullscreenchange', () => { fullscreen.textContent = document.fullscreenElement ? 'Keluar Fullscreen' : 'Fullscreen'; });
    if (!preview) {
        try { const saved = JSON.parse(localStorage.getItem(storageKey)); if (saved) render(saved); } catch {}
    }
    window.addEventListener('offline', () => { connected = false; clearMedia(); status(); play(); });
    window.addEventListener('online', poll);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
    setInterval(status, 1000);
    if (previewPhase) status();
    poll();
})();
