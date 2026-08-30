/**
 * PWA Install Prompt
 *
 * beforeinstallprompt ditangkap di <head> (window.__pwaPrompt)
 * supaya tidak terlewat saat Vite module belum load.
 *
 * prompt() dipanggil sinkron di klik — tanpa await di depan,
 * karena Chrome membatalkan dialog jika gesture user sudah hilang.
 */

const PWA_INSTALLED_KEY = 'masjidnusa_pwa_installed';
const PWA_SESSION_KEY   = 'masjidnusa_pwa_session_dismissed';

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

function triggerNativeInstall() {
    const promptEvent = window.__pwaPrompt;
    if (!promptEvent) {
        return;
    }

    promptEvent.prompt();
    promptEvent.userChoice.then(({ outcome }) => {
        if (outcome === 'accepted') {
            localStorage.setItem(PWA_INSTALLED_KEY, 'true');
            hideInstallPopup();
        }
        window.__pwaPrompt = null;
    }).catch(() => {
        window.__pwaPrompt = null;
    });
}

window.addEventListener('appinstalled', () => {
    localStorage.setItem(PWA_INSTALLED_KEY, 'true');
    window.__pwaPrompt = null;
    hideInstallPopup();
});

document.addEventListener('DOMContentLoaded', () => {
    if (shouldShowPopup()) {
        setTimeout(showInstallPopup, 800);
    }

    document.getElementById('pwa-dismiss-btn')?.addEventListener('click', dismissPopup);
    document.getElementById('pwa-dismiss-btn-2')?.addEventListener('click', dismissPopup);
    document.getElementById('pwa-install-btn')?.addEventListener('click', triggerNativeInstall);
    document.getElementById('pwa-install-popup')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) dismissPopup();
    });
});

window.masjidNusaPWA = { dismissPopup, showInstallPopup };
