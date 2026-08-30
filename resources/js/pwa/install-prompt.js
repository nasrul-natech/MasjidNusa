/**
 * PWA Install Prompt Manager
 *
 * Android (Chrome / Edge / Samsung / Opera):
 *   Popup muncul HANYA setelah beforeinstallprompt siap → tombol langsung trigger instal native.
 *
 * iOS (Safari):
 *   Popup + panduan Add to Home Screen (iOS tidak mendukung instal programmatic).
 */

const PWA_INSTALLED_KEY = 'masjidnusa_pwa_installed';
const PWA_SESSION_KEY   = 'masjidnusa_pwa_session_dismissed';

let deferredPrompt = null;
let androidPopupQueued = false;

// ─── Deteksi platform ─────────────────────────────────────

function isPWAInstalled() {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true ||
        localStorage.getItem(PWA_INSTALLED_KEY) === 'true'
    );
}

function isIOS() {
    return (
        /iphone|ipad|ipod/i.test(navigator.userAgent) ||
        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
    );
}

function isAndroid() {
    return /android/i.test(navigator.userAgent);
}

function isMobile() {
    return isIOS() || isAndroid() || /mobile/i.test(navigator.userAgent);
}

function isPageReload() {
    try {
        const nav = performance.getEntriesByType('navigation')[0];
        if (nav) return nav.type === 'reload';
    } catch (_) {}

    return performance.navigation?.type === 1;
}

function shouldShowPopup() {
    if (isPWAInstalled()) return false;
    if (!isMobile()) return false;

    if (isPageReload()) {
        sessionStorage.removeItem(PWA_SESSION_KEY);
        return true;
    }

    return sessionStorage.getItem(PWA_SESSION_KEY) !== 'true';
}

function getBaseUrl() {
    const meta = document.querySelector('meta[name="base-url"]');
    return meta?.content || '/';
}

// ─── Service Worker (syarat beforeinstallprompt di Android) ──

async function ensureServiceWorker() {
    if (!('serviceWorker' in navigator)) return null;

    try {
        const baseUrl = getBaseUrl();
        return await navigator.serviceWorker.register(`${baseUrl}sw.js`, { scope: baseUrl });
    } catch (error) {
        console.warn('[PWA] Service worker registration failed:', error);
        return null;
    }
}

// ─── Popup UI ─────────────────────────────────────────────

function showInstallPopup() {
    const popup = document.getElementById('pwa-install-popup');
    if (!popup) return;

    const androidContent = document.getElementById('pwa-android-content');
    const iosContent     = document.getElementById('pwa-ios-content');
    if (!androidContent || !iosContent) return;

    if (isIOS()) {
        androidContent.classList.add('hidden');
        iosContent.classList.remove('hidden');
    } else {
        iosContent.classList.add('hidden');
        androidContent.classList.remove('hidden');
    }

    popup.classList.remove('hidden');
    popup.classList.add('flex');
}

function hideInstallPopup() {
    const popup = document.getElementById('pwa-install-popup');
    if (!popup) return;
    popup.classList.add('hidden');
    popup.classList.remove('flex');
}

function dismissPopup() {
    sessionStorage.setItem(PWA_SESSION_KEY, 'true');
    hideInstallPopup();
}

function showAndroidInstallPopup() {
    if (!deferredPrompt || !shouldShowPopup() || isIOS()) return;

    if (document.readyState === 'loading') {
        androidPopupQueued = true;
        return;
    }

    showInstallPopup();
}

async function triggerNativeInstall() {
    if (!deferredPrompt) return;

    try {
        await deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;

        if (outcome === 'accepted') {
            localStorage.setItem(PWA_INSTALLED_KEY, 'true');
        }
    } catch (error) {
        console.warn('[PWA] Install prompt failed:', error);
    } finally {
        deferredPrompt = null;
        hideInstallPopup();
    }
}

// ─── Tangkap event native SEBELUM DOM ready ───────────────

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    showAndroidInstallPopup();
});

window.addEventListener('appinstalled', () => {
    localStorage.setItem(PWA_INSTALLED_KEY, 'true');
    deferredPrompt = null;
    hideInstallPopup();
});

// ─── Init setelah DOM ready ───────────────────────────────

document.addEventListener('DOMContentLoaded', async () => {
    await ensureServiceWorker();

    if (androidPopupQueued && deferredPrompt) {
        showAndroidInstallPopup();
        androidPopupQueued = false;
    }

    if (isIOS() && shouldShowPopup()) {
        setTimeout(showInstallPopup, 1500);
    }

    document.getElementById('pwa-dismiss-btn')
        ?.addEventListener('click', dismissPopup);
    document.getElementById('pwa-dismiss-btn-2')
        ?.addEventListener('click', dismissPopup);

    document.getElementById('pwa-install-btn')
        ?.addEventListener('click', triggerNativeInstall);

    document.getElementById('pwa-install-popup')
        ?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) dismissPopup();
        });
});

window.masjidNusaPWA = { dismissPopup, showInstallPopup };
