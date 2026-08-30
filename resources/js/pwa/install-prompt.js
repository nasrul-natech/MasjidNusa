/**
 * PWA Install Prompt Manager
 *
 * - Tampil setiap refresh/navigasi (kecuali sudah install atau dismiss sesi ini)
 * - Android: tombol Instal aktif setelah beforeinstallprompt siap
 * - iOS: panduan Add to Home Screen
 */

const PWA_INSTALLED_KEY = 'masjidnusa_pwa_installed';
const PWA_SESSION_KEY   = 'masjidnusa_pwa_session_dismissed';

let deferredPrompt = null;

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

function updateInstallButton() {
    const btn = document.getElementById('pwa-install-btn');
    if (!btn || isIOS()) return;

    if (deferredPrompt) {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
        btn.textContent = '';
        btn.innerHTML = `
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Instal Aplikasi
        `;
    } else {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        btn.innerHTML = `
            <svg class="w-5 h-5 mr-2 flex-shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            Menyiapkan instalasi&hellip;
        `;
    }
}

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
        updateInstallButton();
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

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    updateInstallButton();
});

window.addEventListener('appinstalled', () => {
    localStorage.setItem(PWA_INSTALLED_KEY, 'true');
    deferredPrompt = null;
    hideInstallPopup();
});

document.addEventListener('DOMContentLoaded', async () => {
    await ensureServiceWorker();

    if (shouldShowPopup()) {
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
