/**
 * Push Notification Manager
 * Handles VAPID-based web push subscription
 */

const PUSH_SUBSCRIBED_KEY = 'masjidnusa_push_subscribed';

function getBaseUrl() {
    const meta = document.querySelector('meta[name="base-url"]');
    return meta ? meta.content : '/';
}

async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) return null;

    try {
        const baseUrl = getBaseUrl();
        const swUrl = baseUrl + 'sw.js';
        const registration = await navigator.serviceWorker.register(swUrl, { scope: baseUrl });
        return registration;
    } catch (error) {
        console.error('SW registration failed:', error);
        return null;
    }
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

async function subscribeToPush(registration) {
    const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
    if (!vapidPublicKey) return;

    try {
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
        });

        const baseUrl = getBaseUrl();
        const response = await fetch(baseUrl + 'api/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify(subscription)
        });

        if (response.ok) {
            localStorage.setItem(PUSH_SUBSCRIBED_KEY, 'true');
        }
    } catch (error) {
        console.error('Push subscription failed:', error);
    }
}

async function requestNotificationPermission(registration) {
    if (!('Notification' in window)) return;

    if (Notification.permission === 'granted') {
        await subscribeToPush(registration);
        return;
    }

    if (Notification.permission !== 'denied') {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            await subscribeToPush(registration);
        }
    }
}

async function initPushNotifications() {
    // Daftarkan SW saja — izin notifikasi diminta di halaman Pengaturan Adzan
    await registerServiceWorker();
}

document.addEventListener('DOMContentLoaded', initPushNotifications);

window.masjidNusaPush = {
    registerServiceWorker,
    subscribeToPush,
    requestPermission: async () => {
        const reg = await registerServiceWorker();
        if (reg) await requestNotificationPermission(reg);
        return reg;
    },
    getPermission: () => ('Notification' in window ? Notification.permission : 'unsupported'),
};
