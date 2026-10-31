const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const pureContext = {module: {exports: {}}};
vm.runInNewContext(readFileSync(path.join(__dirname, '../../public/js/mosque-display.js'), 'utf8'), pureContext);
const { prayerState, timestamp } = pureContext.module.exports;

const prayerFeed = () => ({
    prayer_mode_enabled: true, adhan_minutes: 3, iqamah_minutes: 10, prayer_minutes: 15,
    prayers: [
        {key: 'subuh', name: 'Subuh', time: '04:30', at: '2026-10-31T04:30:00+07:00'},
        {key: 'dzuhur', name: 'Dzuhur', time: '12:00', at: '2026-10-31T12:00:00+07:00'},
        {key: 'ashar', name: 'Ashar', time: '15:00', at: '2026-10-31T15:00:00+07:00'},
        {key: 'maghrib', name: 'Maghrib', time: '18:00', at: '2026-10-31T18:00:00+07:00'},
        {key: 'isya', name: 'Isya', time: '19:00', at: '2026-10-31T19:00:00+07:00'},
    ],
    next_prayer: {key: 'subuh', name: 'Subuh', at: '2026-11-01T04:30:00+07:00'},
    previous_prayer: {key: 'isya', name: 'Isya', at: '2026-10-30T19:00:00+07:00'},
});

test('prayer phases use absolute half-open windows, countdowns, and exact end', () => {
    const feed = prayerFeed();
    const start = Date.parse(feed.prayers[0].at);
    for (const [elapsed, mode, remaining] of [[-1, 'normal'], [0, 'adhan', 180], [179999, 'adhan', 1], [180000, 'iqamah', 600], [779999, 'iqamah', 1], [780000, 'prayer', 900], [1679999, 'prayer', 1], [1680000, 'normal']]) {
        const state = prayerState(feed, start + elapsed, true);
        assert.equal(state.mode, mode, `elapsed ${elapsed}`);
        assert.equal(state.remaining, remaining);
    }
    assert.equal(prayerState(feed, start, true).current.key, 'subuh');
    assert.equal(prayerState(feed, start, true).upcoming.key, 'dzuhur');
});

test('mode opt-in, offline/stale guard, invalid times, and bounded defaults', () => {
    const feed = prayerFeed(), now = Date.parse(feed.prayers[0].at);
    for (const enabled of [undefined, false, 'true', 1]) assert.equal(prayerState({...feed, prayer_mode_enabled: enabled}, now, true).mode, 'normal');
    assert.equal(JSON.stringify(prayerState(feed, now, false)), JSON.stringify({mode: 'normal', current: null, upcoming: null}));
    assert.equal(prayerState(feed, NaN, true).mode, 'normal');
    for (const bad of ['broken', '2026-10-31T04:30:00', '2026-02-30T04:30:00Z', '2026-10-31T24:00:00Z', null, 1793395800000]) {
        assert.ok(Number.isNaN(timestamp(bad)));
        assert.equal(prayerState({...feed, prayers: [{...feed.prayers[0], at: bad}]}, now, true).mode, 'normal');
    }
    assert.equal(prayerState({...feed, adhan_minutes: 500, iqamah_minutes: -1, prayer_minutes: '60'}, now + 180000, true).mode, 'iqamah');
    assert.equal(prayerState({...feed, iqamah_minutes: 0}, now + 180000, true).mode, 'prayer');
    assert.equal(prayerState({...feed, adhan_minutes: 15, iqamah_minutes: 30, prayer_minutes: 60}, now + 104 * 60000, true).mode, 'prayer');
    assert.equal(prayerState({...feed, adhan_minutes: 15, iqamah_minutes: 30, prayer_minutes: 60}, now + 105 * 60000, true).mode, 'normal');
});

test('tomorrow upcoming and previous-day active session survive midnight without local-clock parsing', () => {
    const feed = prayerFeed();
    assert.equal(prayerState(feed, Date.parse('2026-10-31T23:00:00+07:00'), true).upcoming, feed.next_prayer);
    feed.previous_prayer.at = '2026-10-30T23:50:00+07:00';
    const state = prayerState(feed, Date.parse('2026-10-31T00:05:00+07:00'), true);
    assert.equal(state.mode, 'prayer');
    assert.equal(state.current, feed.previous_prayer);
    assert.equal(state.upcoming.key, 'subuh');
    feed.prayers.push({key: 'imsak', at: 'bad'}, {key: 'sunrise', at: 'bad'});
    assert.equal(prayerState(feed, Date.parse('2026-10-31T00:05:00+07:00'), true).mode, 'prayer');
});

function playerHarness(initial, saved = null, options = {}) {
    let now = Date.parse('2026-10-31T04:30:00+07:00'), elapsed = 0, feed = initial, statusTick, timerId = 0;
    const elements = new Map(), listeners = {}, styles = {}, images = [], timers = new Map(), revoked = [];
    const element = id => {
        if (!elements.has(id)) elements.set(id, {
            textContent: '', hidden: false, children: [],
            append(...nodes) { for (const node of nodes) { node.parent = this; this.children.push(node); } },
            replaceChildren() { this.children = []; },
            querySelectorAll() { return [...this.children]; },
            addEventListener() {},
        });
        return elements.get(id);
    };
    const body = {dataset: {preview: '1', feed: '/preview'}, style: {setProperty: (key, value) => { styles[key] = value; }}};
    if (saved) body.dataset.preview = '0';
    Object.assign(body.dataset, options.dataset);
    class Clock extends Date { static now() { return now; } }
    const context = {
        Date: Clock, Intl, AbortController, performance: {now: () => elapsed},
        location: {origin: options.origin ?? 'https://masjid.test', href: `${options.origin ?? 'https://masjid.test'}/display-masjid`, search: options.search ?? ''},
        document: {body, getElementById: element, createElement: tag => {
            const node = {
            contentWindow: tag === 'iframe' ? {postMessage() {}} : undefined,
            append(...nodes) { this.children = nodes; },
            remove() { if (this.parent) this.parent.children = this.parent.children.filter(node => node !== this); },
            };
            if (tag === 'img') images.push(node);
            return node;
        }, addEventListener() {}},
        window: {addEventListener: (event, fn) => { listeners[event] = fn; }, removeEventListener: (event, fn) => { if (listeners[event] === fn) delete listeners[event]; }},
        localStorage: {getItem: () => JSON.stringify(saved), setItem() {}, removeItem() {}},
        fetch: async () => typeof feed === 'number' ? {ok: false, status: feed} : {ok: true, json: async () => feed},
        setTimeout: (fn, ms) => { timers.set(++timerId, {fn, ms}); return timerId; }, clearTimeout: id => timers.delete(id), setInterval: fn => { statusTick = fn; },
        URL: class extends URL { static revokeObjectURL(url) { revoked.push(url); } static createObjectURL() { return `blob:${images.length}`; } },
    };
    if (options.storageUnavailable) context.localStorage.removeItem = () => { throw new Error('Storage denied'); };
    if (options.cacheDelete) { context.window.caches = {}; context.caches = {delete: options.cacheDelete}; }
    if (options.cacheOpen) { context.window.caches = {}; context.caches = {open: options.cacheOpen}; }
    vm.runInNewContext(readFileSync(path.join(__dirname, '../../public/js/mosque-display.js'), 'utf8'), context);
    return {body, element, listeners, styles, images, timers, revoked,
        tick: milliseconds => { now += milliseconds; elapsed += milliseconds; statusTick(); },
        jumpDevice: milliseconds => { now += milliseconds; },
        fire: ms => { const entry = [...timers].find(([, timer]) => timer.ms === ms); assert.ok(entry, `timer ${ms}`); timers.delete(entry[0]); entry[1].fn(); },
        setFeed: value => { feed = value; }};
}

const completeFeed = () => ({...prayerFeed(), server_now: '2026-10-31T04:30:00+07:00', prayer_date: '2026-10-31', timezone: 'Asia/Jakarta', title: 'Masjid', activities: [], items: [{type: 'youtube', youtube_id: 'abcdefghijk', title: 'Video', duration_seconds: 30}], published_at: null});
const settle = () => new Promise(resolve => setImmediate(resolve));

test('live prayer mode stops video, filters footer, finishes confirmed sequence offline, and never starts cached mode', async () => {
    const feed = completeFeed(), player = playerHarness({...feed, prayer_mode_enabled: false});
    await settle();
    assert.equal(player.element('media').children.length, 1);
    feed.prayers.push({key: 'terbit', name: 'Terbit', time: '06:00'}, {key: 'imsak', name: 'Imsak', time: '04:20'});
    feed.previous_prayer = {...feed.prayers[0]};
    feed.next_prayer = {...feed.prayers[1]};
    player.setFeed(feed); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'adhan');
    assert.equal(player.element('media').children.length, 0);
    assert.equal(player.element('prayers').children.length, 7);
    assert.equal(player.element('prayers').children[0].className, 'extra');
    assert.equal(player.element('prayers').children[1].className, 'current');
    assert.equal(player.element('prayers').children[3].className, 'upcoming');
    player.listeners.offline();
    assert.equal(player.body.dataset.mode, 'adhan');
    assert.equal(player.element('clock').textContent, '--:--:--');
    assert.equal(player.element('date').textContent, '');
    assert.equal(player.element('media').children.length, 0);
    const cached = playerHarness(503, feed); await settle();
    assert.equal(cached.body.dataset.mode, 'normal');
    assert.equal(cached.element('clock').textContent, '--:--:--');
});

test('stale heartbeat and corrupt timestamps preserve confirmed sequence but publication removal ends it', async () => {
    const feed = completeFeed(), player = playerHarness(feed); await settle();
    player.tick(76000);
    assert.equal(player.body.dataset.mode, 'adhan');
    assert.equal(player.element('clock').textContent, '--:--:--');
    player.setFeed({...feed, server_now: '2026-10-31T04:31:16+07:00'}); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'adhan');
    player.setFeed({...feed, server_now: 'corrupt'}); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'adhan');
    assert.equal(player.element('clock').textContent, '--:--:--');
    player.setFeed({...feed, server_now: '2026-10-31T04:31:16+07:00'}); await player.listeners.online();
    player.setFeed({...feed, prayers: [{...feed.prayers[0], at: 'broken'}]}); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'adhan');
    const publicPlayer = playerHarness(feed, {...feed, items: []}); await settle();
    assert.equal(publicPlayer.body.dataset.mode, 'adhan');
    publicPlayer.setFeed(404); await publicPlayer.listeners.online();
    assert.equal(publicPlayer.body.dataset.mode, 'normal');
    assert.equal(publicPlayer.element('clock').textContent, '--:--:--');
    assert.equal(publicPlayer.element('announcement').textContent, '');
});

test('disconnect during dark prayer finishes original deadline without starting future offline prayers', async () => {
    const player = playerHarness(completeFeed()); await settle();
    player.tick(13 * 60000);
    assert.equal(player.body.dataset.mode, 'prayer');
    player.listeners.offline();
    assert.equal(player.body.dataset.mode, 'prayer');
    player.tick(14 * 60000);
    assert.equal(player.body.dataset.mode, 'prayer');
    player.tick(60000);
    assert.equal(player.body.dataset.mode, 'normal');
    player.tick(8 * 60 * 60000);
    assert.equal(player.body.dataset.mode, 'normal');
});

test('player applies theme and bounded motion/ticker options without external audio', async () => {
    const player = playerHarness({...completeFeed(), theme: 'royal', decoration: 'clouds', announcement_size: 'extra', announcement_seconds: 90, transition_ms: 1200}); await settle();
    assert.equal(player.body.dataset.theme, 'royal');
    assert.equal(player.body.dataset.decoration, 'clouds');
    assert.equal(player.body.dataset.announcementSize, 'extra');
    assert.equal(player.styles['--announcement-seconds'], '90s');
    assert.equal(player.styles['--transition-ms'], '1200ms');
    player.setFeed({...completeFeed(), theme: 'invalid', decoration: 'invalid', announcement_size: 'invalid', announcement_seconds: 1, transition_ms: Infinity}); await player.listeners.online();
    assert.equal(player.body.dataset.theme, 'emerald');
    assert.equal(player.body.dataset.decoration, 'mosque');
    assert.equal(player.body.dataset.announcementSize, 'large');
    assert.equal(player.styles['--announcement-seconds'], '40s');
    assert.equal(player.styles['--transition-ms'], '800ms');
    player.setFeed({...completeFeed(), announcements: ['Subuh', 'Kajian'], logo_url: '/storage/branding/logo.png'});
    await player.listeners.online();
    const ticker = player.element('announcement').children;
    assert.equal(ticker.length, 3);
    assert.equal(ticker[0].textContent, 'Subuh');
    assert.equal(ticker[1].className, 'ticker-logo');
    assert.equal(ticker[1].src, '/storage/branding/logo.png');
    assert.equal(ticker[2].textContent, 'Kajian');
    player.setFeed({...completeFeed(), activities: [{title: 'Kajian Subuh', starts_at: '2026-11-15T04:47:00+07:00'}]});
    await player.listeners.online();
    const headerDate = player.element('date').children;
    assert.equal(headerDate.length, 2);
    assert.match(headerDate[0].textContent, /Sabtu, 31 Oktober 2026/);
    assert.equal(headerDate[1].className, 'date-hijri');
    assert.match(headerDate[1].textContent, /1448 H/);
    const activity = player.element('activities').children[0];
    assert.equal(activity.children[0].textContent, 'Kajian Subuh');
    assert.equal(activity.children[1].className, 'activity-when');
    assert.match(activity.children[1].textContent, /15 Nov 2026/);
    const template = readFileSync(path.join(__dirname, '../../resources/views/mosque-display/screen.blade.php'), 'utf8');
    assert.match(template, /prefers-reduced-motion:reduce/);
    assert.match(template, /body\[data-mode=prayer\] #mode-title,body\[data-mode=prayer\] #mode-countdown,body\[data-mode=prayer\] #mode-note\{display:none\}/);
    assert.match(template, /body:not\(\[data-mode=normal\]\) #tv-tools\{display:none\}/);
});

test('media layout supports full screen and cover with safe legacy defaults', async () => {
    const feed = {...completeFeed(), layout: 'immersive', media_fit: 'cover'};
    const player = playerHarness(feed); await settle();
    assert.equal(player.body.dataset.layout, 'immersive');
    assert.equal(player.body.dataset.mediaFit, 'cover');
    player.setFeed({...feed, layout: 'invalid', media_fit: 'stretch'}); await player.listeners.online();
    assert.equal(player.body.dataset.layout, 'focus');
    assert.equal(player.body.dataset.mediaFit, 'contain');
    const template = readFileSync(path.join(__dirname, '../../resources/views/mosque-display/screen.blade.php'), 'utf8');
    assert.match(template, /body\[data-layout=immersive\] #body\{position:absolute;inset:0;/);
    assert.match(template, /body\[data-layout=immersive\] header\{[^}]*z-index:2;[^}]*linear-gradient\([^}]*pointer-events:none/);
    assert.match(template, /body\[data-layout=immersive\] \.footer-frame\{[^}]*inset:auto 1\.1vmin 0/);
    assert.match(template, /\.header-center\{[^}]*justify-self:center/);
    assert.doesNotMatch(template, /body\[data-layout=immersive\] header[^}]*display:none/);
});

test('seasonal scenery uses safe presets and dark prayer keeps only dim animated clock card', async () => {
    for (const decoration of ['mosque', 'clouds', 'ramadan', 'qurban', 'desert', 'stars', 'none']) {
        const player = playerHarness({...completeFeed(), decoration}); await settle();
        assert.equal(player.body.dataset.decoration, decoration);
    }
    const template = readFileSync(path.join(__dirname, '../../resources/views/mosque-display/screen.blade.php'), 'utf8');
    assert.match(template, /body\[data-mode=prayer\] #prayer-mode \.tv-scenery\{display:none\}/);
    assert.match(template, /prayer-clock-drift 80s/);
    assert.match(template, /prayer-mode-glow/);
    assert.match(template, /body\[data-theme=daylight\]/);
    assert.match(template, /#prayers\{[^}]*gap:0/);
    assert.match(template, /status-badge\[data-state=online\]/);
    assert.match(template, /prefers-reduced-motion:reduce/);
});

test('YouTube uses document and iframe referrers plus safe actual origin', async () => {
    const feed = {...completeFeed(), prayer_mode_enabled: false};
    for (const origin of ['https://masjid.test', 'http://localhost:8000', 'null', 'javascript:alert(1)', 'https://masjid.test/path']) {
        const player = playerHarness(feed, null, {origin}); await settle();
        const frame = player.element('media').children[0], url = new URL(frame.src);
        assert.equal(frame.referrerPolicy, 'strict-origin-when-cross-origin');
        assert.equal(url.origin, 'https://www.youtube-nocookie.com');
        assert.equal(url.searchParams.get('enablejsapi'), '1');
        const expectedOrigin = ['https://masjid.test', 'http://localhost:8000'].includes(origin) ? origin : (origin === 'https://masjid.test/path' ? 'https://masjid.test' : null);
        assert.equal(url.searchParams.get('origin'), expectedOrigin);
        assert.equal(player.element('media-message').hidden, false);
        assert.match(player.element('media-message').textContent, /Memuat YouTube/i);
    }
    const template = readFileSync(path.join(__dirname, '../../resources/views/mosque-display/screen.blade.php'), 'utf8');
    assert.match(template, /<meta name="referrer" content="strict-origin-when-cross-origin">/);
});

test('YouTube trusted error and network failure leave clear fallback; spoofed or stale messages ignored', async () => {
    const feed = {...completeFeed(), prayer_mode_enabled: false, items: [{type: 'youtube', youtube_id: 'abcdefghijk', title: 'Video', duration_seconds: 35}]};
    const player = playerHarness(feed); await settle();
    let frame = player.element('media').children[0];
    const post = event => player.listeners.message(event);
    post({origin: 'https://evil.test', source: frame.contentWindow, data: JSON.stringify({event: 'onError', info: 150})});
    post({origin: 'https://www.youtube-nocookie.com', source: {}, data: JSON.stringify({event: 'onError', info: 153})});
    post({origin: 'https://www.youtube-nocookie.com', source: frame.contentWindow, data: 'bad JSON'});
    assert.equal(player.element('media').children.length, 1);
    post({origin: 'https://www.youtube-nocookie.com', source: frame.contentWindow, data: JSON.stringify({event: 'onStateChange', info: 1})});
    assert.equal(player.element('media-message').hidden, true);
    post({origin: 'https://www.youtube-nocookie.com', source: frame.contentWindow, data: JSON.stringify({event: 'onError', info: 150})});
    frame = player.element('media').children[0];
    assert.equal(new URL(frame.src).host, 'www.youtube.com');
    post({origin: 'https://www.youtube.com', source: frame.contentWindow, data: JSON.stringify({event: 'onError', info: 150})});
    assert.equal(player.element('media').children.length, 0);
    assert.equal(player.element('fallback').hidden, false);
    assert.match(player.element('fallback').textContent, /tidak tersedia|embed/i);
    player.fire(35000);
    const replacement = player.element('media').children[0];
    assert.ok(replacement);
    const staleWindow = replacement.contentWindow;
    post({origin: 'https://www.youtube-nocookie.com', source: staleWindow, data: JSON.stringify({event: 'onError', info: 150})});
    frame = player.element('media').children[0];
    assert.equal(new URL(frame.src).host, 'www.youtube.com');
    post({origin: 'https://www.youtube-nocookie.com', source: staleWindow, data: JSON.stringify({event: 'onError', info: 150})});
    assert.equal(player.element('media').children[0], frame);
    frame.onerror();
    assert.equal(player.element('media').children.length, 0);
    assert.equal(player.element('fallback').hidden, false);
    const offlinePlayer = playerHarness(feed, {}); await settle();
    offlinePlayer.listeners.offline();
    assert.equal(offlinePlayer.listeners.message, undefined);
});

test('preview phases ignore real schedules and use fixed demo defaults without mutating feed', async () => {
    for (const [phase, countdown] of [['adhan', '03:00'], ['iqamah', '10:00'], ['prayer', '15:00']]) {
        const feed = {...completeFeed(), prayer_mode_enabled: false, adhan_minutes: 9, iqamah_minutes: 25, prayer_minutes: 55};
        const before = JSON.stringify(feed);
        const player = playerHarness(feed, null, {dataset: {previewPhase: phase}}); await settle();
        assert.equal(player.body.dataset.mode, phase);
        assert.equal(player.element('mode-countdown').textContent, countdown);
        assert.equal(player.element('media').children.length, 0);
        assert.notEqual(player.element('mode-clock').textContent, '--:--:--');
        player.tick(3600000);
        assert.equal(player.body.dataset.mode, phase);
        assert.equal(player.element('mode-countdown').textContent, countdown);
        assert.equal(JSON.stringify(feed), before);
    }
    const offline = playerHarness(503, null, {dataset: {previewPhase: 'iqamah'}}); await settle();
    assert.equal(offline.body.dataset.mode, 'iqamah');
    assert.equal(offline.element('mode-countdown').textContent, '10:00');
});

test('public query and dataset cannot simulate; preview needs approved valid body phase', async () => {
    const feed = {...completeFeed(), prayer_mode_enabled: false};
    for (const dataset of [{preview: '0', previewPhase: 'prayer'}, {preview: '1'}, {preview: '1', previewPhase: 'invalid'}, {preview: '1', previewPhase: 'ADHAN'}]) {
        const player = playerHarness(feed, null, {dataset, search: '?preview_phase=prayer&simulate=adhan'}); await settle();
        assert.equal(player.body.dataset.mode, 'normal');
        assert.equal(player.element('media').children.length, 1);
    }
    const real = playerHarness(completeFeed(), null, {dataset: {preview: '0', previewPhase: 'prayer'}, search: '?preview_phase=prayer'}); await settle();
    assert.equal(real.body.dataset.mode, 'adhan');
});

test('all visual phases render then normal video resumes at configured end', async () => {
    const feed = completeFeed(), player = playerHarness(feed); await settle();
    assert.equal(player.element('mode-title').textContent, 'Waktu Adzan Subuh');
    assert.match(player.element('mode-note').textContent, /visual/);
    for (const [time, mode, countdown] of [['04:33:00', 'iqamah', '10:00'], ['04:43:00', 'prayer', '15:00'], ['04:58:00', 'normal', null]]) {
        player.setFeed({...feed, server_now: `2026-10-31T${time}+07:00`}); await player.listeners.online();
        assert.equal(player.body.dataset.mode, mode);
        if (countdown) assert.equal(player.element('mode-countdown').textContent, countdown);
        if (mode === 'prayer') {
            assert.equal(player.element('mode-title').textContent, '');
            assert.equal(player.element('mode-note').textContent, '');
            assert.equal(player.element('media').children.length, 0);
        }
    }
    assert.equal(player.element('media').children.length, 1);
    player.setFeed({...feed, server_now: '2026-10-31T04:58:00+07:00', prayer_date: '2026-10-30'}); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'normal');
    assert.match(player.element('prayers').textContent, /tidak terverifikasi/);
    assert.notEqual(player.element('clock').textContent, '--:--:--');
});

test('midnight tick keeps adjacent-day active session without polling, but rejects old schedules', async () => {
    const feed = completeFeed();
    feed.server_now = '2026-10-31T23:59:45+07:00';
    feed.prayers[4].at = '2026-10-31T23:50:00+07:00';
    feed.previous_prayer = {...feed.prayers[4]};
    const player = playerHarness(feed); await settle();
    assert.equal(player.body.dataset.mode, 'iqamah');
    player.tick(30000);
    assert.equal(player.body.dataset.mode, 'iqamah');
    assert.equal(player.element('mode-countdown').textContent, '02:45');
    assert.notEqual(player.element('clock').textContent, '--:--:--');
    assert.match(player.element('prayers').textContent, /tidak terverifikasi/);
    player.setFeed({...feed, prayer_date: '2026-10-29', server_now: '2026-11-01T00:00:15+07:00'}); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'normal');
});

test('403/404 clear screen before failing or hanging storage cleanup', async () => {
    for (const code of [403, 404]) {
        let cleanupStarted = false;
        const feed = completeFeed();
        const player = playerHarness(feed, {...feed, items: []}, {storageUnavailable: true, cacheDelete: () => { cleanupStarted = true; return new Promise(() => {}); }});
        await settle(); assert.equal(player.body.dataset.mode, 'adhan');
        player.setFeed(code); player.listeners.online(); await settle();
        assert.equal(cleanupStarted, true);
        assert.equal(player.body.dataset.mode, 'normal');
        assert.equal(player.element('clock').textContent, '--:--:--');
        assert.equal(player.element('announcement').textContent, '');
        assert.equal(player.element('media').children.length, 0);
        assert.match(player.element('fallback').textContent, /dinonaktifkan/);
    }
});

test('slow photo retains outgoing until load, starts duration after load, and bounds failures', async () => {
    const feed = {...completeFeed(), prayer_mode_enabled: false, items: [
        {type: 'photo', title: 'First', photo_url: '/first.jpg', duration_seconds: 20},
        {type: 'photo', title: 'Second', photo_url: '/second.jpg', duration_seconds: 25},
    ]};
    const player = playerHarness(feed); await settle();
    const first = player.images.at(-1);
    assert.equal(player.element('media').children.length, 0);
    assert.equal([...player.timers.values()].some(timer => timer.ms === 20000), false);
    first.onload();
    assert.equal(player.element('media').children[0], first);
    player.fire(20000);
    const second = player.images.at(-1);
    assert.notEqual(second, first);
    assert.equal(player.element('media').children[0], first);
    assert.equal([...player.timers.values()].some(timer => timer.ms === 25000), false);
    second.onload();
    assert.equal(player.element('media').children.length, 1);
    assert.equal(player.element('media').children[0], second);
    player.fire(25000);
    const staleLoad = player.images.at(-1).onload;
    player.fire(15000);
    staleLoad();
    assert.equal(player.element('media').children[0], second);
    player.fire(1000);
    const invalidated = player.images.at(-1).onload;
    player.setFeed({...feed, version: 2, items: []}); await player.listeners.online();
    invalidated();
    assert.equal(player.element('media').children.length, 0);
});

test('pending photos cannot inject after prayer entry or offline invalidation', async () => {
    const feed = {...completeFeed(), prayer_mode_enabled: false, items: [{type: 'photo', title: 'Photo', photo_url: '/photo.jpg', duration_seconds: 20}]};
    const player = playerHarness(feed); await settle();
    const pending = player.images.at(-1).onload;
    player.setFeed({...feed, prayer_mode_enabled: true}); await player.listeners.online();
    pending();
    assert.equal(player.body.dataset.mode, 'adhan');
    assert.equal(player.element('media').children.length, 0);
    player.setFeed(feed); await player.listeners.online();
    const offlinePending = player.images.at(-1).onload;
    player.listeners.offline(); offlinePending();
    assert.equal(player.element('media').children.length, 0);
});

test('offline blob URLs release only on replacement, load failure, or invalidation', async () => {
    const feed = {...completeFeed(), prayer_mode_enabled: false, items: [
        {type: 'photo', title: 'One', photo_url: '/one.jpg', duration_seconds: 20, scheduled: false},
        {type: 'photo', title: 'Two', photo_url: '/two.jpg', duration_seconds: 25, scheduled: false},
    ]};
    const player = playerHarness(503, feed, {cacheOpen: async () => ({match: async () => ({blob: async () => ({})})})});
    await settle();
    player.images.at(-1).onload();
    const firstUrl = player.element('media').children[0].src;
    assert.deepEqual(player.revoked, []);
    player.fire(20000); await settle();
    assert.equal(player.revoked.includes(firstUrl), false);
    const pendingUrl = player.images.at(-1).src;
    player.images.at(-1).onerror();
    assert.ok(player.revoked.includes(pendingUrl));
    assert.equal(player.revoked.includes(firstUrl), false);
    player.fire(1000); await settle(); player.images.at(-1).onload();
    assert.ok(player.revoked.includes(firstUrl));
    const activeUrl = player.element('media').children[0].src;
    player.listeners.offline();
    assert.ok(player.revoked.includes(activeUrl));
});

test('disable invalidates pending photo callback even when cache cleanup rejects', async () => {
    const feed = {...completeFeed(), prayer_mode_enabled: false, items: [{type: 'photo', title: 'Photo', photo_url: '/photo.jpg', duration_seconds: 20}]};
    const player = playerHarness(feed, {...feed, items: []}, {storageUnavailable: true, cacheDelete: async () => { throw new Error('Cache denied'); }});
    await settle(); const stale = player.images.at(-1).onload;
    player.setFeed(404); await player.listeners.online(); stale();
    assert.equal(player.element('media').children.length, 0);
    assert.equal(player.element('announcement').textContent, '');
    assert.match(player.element('fallback').textContent, /dinonaktifkan/);
});

test('regressed server timestamps fail closed against monotonic baseline, not device wall clock', async () => {
    const feed = completeFeed(), player = playerHarness(feed); await settle();
    player.tick(30000);
    player.setFeed({...feed, server_now: '2026-10-31T04:30:00+07:00'}); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'adhan');
    assert.equal(player.element('clock').textContent, '--:--:--');
    player.jumpDevice(86400000 * 90);
    player.setFeed({...feed, server_now: '2026-10-31T04:30:27+07:00'}); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'adhan');
    assert.equal(player.element('mode-countdown').textContent, '02:33');
    player.setFeed({...feed, server_now: '2026-10-31T04:29:00+07:00'}); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'adhan');
    player.setFeed({...feed, server_now: '2026-10-31T04:30:30+07:00'}); await player.listeners.online();
    assert.equal(player.body.dataset.mode, 'adhan');
});

test('service worker preserves explicit TV photo cache during activation', async () => {
    const listeners = {}, deleted = [];
    const context = {
        self: { addEventListener: (name, handler) => { listeners[name] = handler; }, clients: { claim() {} } },
        caches: {
            keys: async () => ['masjidnusa-v5-static', 'masjidnusa-v6-static', 'masjidnusa-v6-dynamic', 'mosque-display-public-photos-v1'],
            delete: async key => { deleted.push(key); },
        },
    };
    vm.runInNewContext(readFileSync(path.join(__dirname, '../../public/sw.js'), 'utf8'), context);
    let pending;
    listeners.activate({ waitUntil: promise => { pending = promise; } });
    await pending;
    assert.deepEqual(deleted, ['masjidnusa-v5-static']);
});

test('late offline photo blob cannot reappear after media generation changes', async () => {
    let resolveBlob, blobStarted = false, createdUrls = 0;
    const blob = new Promise(resolve => { resolveBlob = resolve; });
    const listeners = {}, elements = new Map(), images = [];
    const element = id => {
        if (!elements.has(id)) elements.set(id, {
            textContent: '', hidden: false, children: [],
            append(node) { this.children.push(node); },
            replaceChildren() { this.children = []; },
            querySelectorAll() { return this.children; },
            addEventListener() {},
        });
        return elements.get(id);
    };
    const saved = {
        version: 1, items: [{ type: 'photo', title: 'Foto lama', photo_url: 'https://masjid.test/display-masjid/photos/photo.jpg', scheduled: false, duration_seconds: 15 }],
        timezone: 'Asia/Jakarta', title: 'Masjid', announcement: 'Pengumuman', prayers: [], activities: [], prayer_date: '2026-10-30', published_at: null,
    };
    const context = {
        document: {
            body: { dataset: { preview: '0', feed: '/display-masjid/feed' }, style: { setProperty() {} } },
            getElementById: element,
            createElement: () => { const image = { remove() {} }; images.push(image); return image; },
            addEventListener() {}, hidden: false,
        },
        window: { addEventListener: (name, handler) => { listeners[name] = handler; }, caches: {} },
        localStorage: { getItem: () => JSON.stringify(saved) },
        caches: { open: async () => ({ match: async () => ({ blob: () => { blobStarted = true; return blob; } }) }) },
        URL: { createObjectURL: () => { createdUrls++; return 'blob:old'; }, revokeObjectURL() {} },
        fetch: () => new Promise(() => {}), AbortController,
        setTimeout: () => 1, clearTimeout() {}, setInterval() {}, console,
    };
    vm.runInNewContext(readFileSync(path.join(__dirname, '../../public/js/mosque-display.js'), 'utf8'), context);
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(blobStarted, true);
    // Another offline event resets playback while the previous photo still decodes.
    listeners.offline();
    resolveBlob({});
    await new Promise(resolve => setImmediate(resolve));
    // Only current generation may allocate/append; stale continuation must be discarded.
    assert.equal(createdUrls, 1);
    assert.equal(element('media').children.length, 0);
    images.at(-1).onload();
    assert.equal(element('media').children.length, 1);
});

test('fullscreen requires click and rejected browser requests show clear fallback', async () => {
    const elements = new Map(), handlers = {};
    let requests = 0;
    const element = id => {
        if (!elements.has(id)) elements.set(id, { textContent: '', addEventListener: (event, handler) => { handlers[`${id}:${event}`] = handler; } });
        return elements.get(id);
    };
    const context = {
        document: {
            body: { dataset: { preview: '1', feed: '/preview' } },
            getElementById: element, addEventListener() {},
            documentElement: { requestFullscreen: async () => { requests++; throw new Error('Denied'); } },
        },
        window: { addEventListener() {} }, fetch: () => new Promise(() => {}), AbortController,
        setTimeout: () => 1, clearTimeout() {}, setInterval() {},
    };
    vm.runInNewContext(readFileSync(path.join(__dirname, '../../public/js/mosque-display.js'), 'utf8'), context);
    assert.equal(requests, 0);
    await handlers['fullscreen:click']();
    assert.equal(requests, 1);
    assert.match(element('fullscreen-message').textContent, /menu layar penuh browser TV/);
});
