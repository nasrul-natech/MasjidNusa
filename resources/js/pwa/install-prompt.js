/**
 * PWA Install Prompt
 *
 * beforeinstallprompt ditangkap di <head> (window.__pwaPrompt)
 * supaya tidak terlewat saat Vite module belum load.
 *
 * Popup ditampilkan setiap muat halaman jika belum terinstal (standalone / appinstalled).
 * "Nanti saja" hanya menutup untuk halaman ini; halaman berikutnya tampil lagi.
 *
 * prompt() dipanggil sinkron di klik — tanpa await di depan,
 * karena Chrome membatalkan dialog jika gesture user sudah hilang.
 */

const PWA_INSTALLED_KEY = 'masjidnusa_pwa_installed';

function isPWAInstalled() {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.matchMedia('(display-mode: fullscreen)').matches ||
        window.matchMedia('(display-mode: minimal-ui)').matches ||
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

function isMobileLike() {
    return isIOS() || /android|mobile/i.test(navigator.userAgent);
}

function canOfferInstallUi() {
    return isMobileLike() || Boolean(window.__pwaPrompt);
}

function shouldShowPopup() {
    return !isPWAInstalled() && canOfferInstallUi();
}

function syncInstallButton() {
    const btn = document.getElementById('pwa-install-btn');
    if (!btn) return;
    const canNative = Boolean(window.__pwaPrompt);
    btn.disabled = !canNative && !isIOS();
    btn.classList.toggle('opacity-50', btn.disabled);
    btn.classList.toggle('cursor-not-allowed', btn.disabled);
}

function showInstallPopup() {
    if (!shouldShowPopup()) return;

    const popup = document.getElementById('pwa-install-popup');
    if (!popup) return;

    const androidContent = document.getElementById('pwa-android-content');
    const iosContent = document.getElementById('pwa-ios-content');
    if (!androidContent || !iosContent) return;

    if (isIOS()) {
        androidContent.classList.add('hidden');
        iosContent.classList.remove('hidden');
    } else {
        iosContent.classList.add('hidden');
        androidContent.classList.remove('hidden');
        syncInstallButton();
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
    hideInstallPopup();
}

function triggerNativeInstall() {
    const promptEvent = window.__pwaPrompt;
    if (!promptEvent) return;

    promptEvent.prompt();
    promptEvent.userChoice.then(({ outcome }) => {
        if (outcome === 'accepted') {
            localStorage.setItem(PWA_INSTALLED_KEY, 'true');
            hideInstallPopup();
        }
        window.__pwaPrompt = null;
        syncInstallButton();
    }).catch(() => {
        window.__pwaPrompt = null;
        syncInstallButton();
    });
}

function openInstallPopupIfEligible() {
    if (!shouldShowPopup()) {
        hideInstallPopup();
        return;
    }
    showInstallPopup();
}

window.addEventListener('appinstalled', () => {
    localStorage.setItem(PWA_INSTALLED_KEY, 'true');
    window.__pwaPrompt = null;
    hideInstallPopup();
});

function bindInstallPopupUi() {
    document.getElementById('pwa-dismiss-btn')?.addEventListener('click', dismissPopup);
    document.getElementById('pwa-dismiss-btn-2')?.addEventListener('click', dismissPopup);
    document.getElementById('pwa-install-btn')?.addEventListener('click', triggerNativeInstall);
    document.getElementById('pwa-install-popup')?.addEventListener('click', (e) => {
        if (e.target === e.currentTarget) dismissPopup();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bindInstallPopupUi();
    if (isIOS()) {
        setTimeout(openInstallPopupIfEligible, 400);
    } else {
        openInstallPopupIfEligible();
    }
});

window.addEventListener('pwa-install-ready', () => {
    syncInstallButton();
    openInstallPopupIfEligible();
});

window.masjidNusaPWA = { dismissPopup, showInstallPopup: openInstallPopupIfEligible };
