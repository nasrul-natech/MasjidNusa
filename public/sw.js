/**
 * MasjidNusa - Service Worker
 * Handles caching, push notifications, and offline support
 */

const CACHE_VERSION = 'masjidnusa-v2';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;

const STATIC_ASSETS = [
    '/',
    '/offline',
];

// Install: cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {
                // Ignore errors for missing assets during install
            });
        })
    );
    self.skipWaiting();
});

// Activate: clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys
                    .filter((key) => key !== STATIC_CACHE && key !== DYNAMIC_CACHE)
                    .map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// Fetch: Network-first for pages, Cache-first for assets
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET, cross-origin, and API requests
    if (
        request.method !== 'GET' ||
        url.origin !== self.location.origin ||
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/webhook/')
    ) {
        return;
    }

    // Cache-first for static assets (CSS, JS, images, fonts)
    if (
        url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/)
    ) {
        event.respondWith(
            caches.match(request).then((cached) => {
                return cached || fetch(request).then((response) => {
                    const clone = response.clone();
                    caches.open(STATIC_CACHE).then((cache) => cache.put(request, clone));
                    return response;
                });
            })
        );
        return;
    }

    // Network-first for pages
    event.respondWith(
        fetch(request)
            .then((response) => {
                const clone = response.clone();
                caches.open(DYNAMIC_CACHE).then((cache) => cache.put(request, clone));
                return response;
            })
            .catch(() => {
                return caches.match(request).then((cached) => {
                    return cached || caches.match('/offline');
                });
            })
    );
});

// Push notification handler
self.addEventListener('push', (event) => {
    let data = {
        title: 'MasjidNusa',
        body: 'Ada notifikasi baru untuk Anda',
        icon: '/images/icons/icon-192.png',
        badge: '/images/icons/badge-72.png',
        url: '/',
    };

    if (event.data) {
        try {
            const payload = event.data.json();
            data = { ...data, ...payload };
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const isAdzan = data.data && data.data.type === 'adzan';

    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        vibrate: isAdzan ? [200, 100, 200, 100, 200, 100, 200] : [200, 100, 200],
        data: { url: data.data?.url || data.url || '/' },
        actions: isAdzan
            ? [{ action: 'open', title: 'Lihat Jadwal' }, { action: 'dismiss', title: 'Tutup' }]
            : [{ action: 'open', title: 'Buka' }, { action: 'dismiss', title: 'Tutup' }],
        requireInteraction: isAdzan,
        tag: data.tag || 'masjidnusa-notification',
    };

    const showNotif = self.registration.showNotification(data.title, options);

    // For adzan notifications, also tell open clients to play sound
    const notifyClients = isAdzan
        ? self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clients => {
            clients.forEach(client => {
                client.postMessage({
                    type: 'ADZAN_TRIGGER',
                    prayer: data.data.prayer,
                    time: data.data.time,
                });
            });
        })
        : Promise.resolve();

    event.waitUntil(Promise.all([showNotif, notifyClients]));
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss') return;

    const url = event.notification.data?.url || '/';
    const baseUrl = self.location.origin;
    const fullUrl = url.startsWith('http') ? url : baseUrl + url;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(fullUrl);
            }
        })
    );
});
