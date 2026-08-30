/**
 * PWA Install Prompt Manager
 *
 * Behavior:
 *  - Tampil otomatis di SETIAP halaman dibuka/refresh (jika belum install)
 *  - Dismiss → tidak muncul lagi SELAMA navigasi dalam sesi yang sama
 *  - Refresh (reload) → dismiss direset → muncul lagi
 *  - Sudah terinstal → tidak pernah muncul lagi
 *
 * Platform:
 *  - Android Chrome/Edge/Samsung/Opera → tombol Install langsung (beforeinstallprompt)
 *  - iOS Safari/Chrome → panduan Add to Home Screen (satu-satunya cara di iOS)
 *  - iPad iPadOS 13+ → terdeteksi via maxTouchPoints
 */

const PWA_INSTALLED_KEY   = 'masjidnusa_pwa_installed';
const PWA_SESSION_KEY     = 'masjidnusa_pwa_session_dismissed';

let deferredPrompt = null;

// ─── Deteksi ─────────────────────────────────────────────

function isPWAInstalled() {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true ||
        localStorage.getItem(PWA_INSTALLED_KEY) === 'true'
    );
}

function isIOS() {
    // iPhone/iPod/iPad biasa + iPad iPadOS 13+ (UA = "Macintosh")
    return (
        /iphone|ipad|ipod/i.test(navigator.userAgent) ||
        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
    );
}

function isMobile() {
    return (
        /android|iphone|ipad|ipod|mobile/i.test(navigator.userAgent) ||
        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
    );
}

/**
 * Deteksi apakah halaman ini dibuka lewat reload/refresh.
 * Performance Navigation API Level 2 (semua browser modern) →
 * fallback ke Level 1 untuk browser lama.
 */
function isPageReload() {
    try {
        const nav = performance.getEntriesByType('navigation')[0];
        if (nav) return nav.type === 'reload';
    } catch (_) {}
    // Fallback: Performance Navigation Level 1
    return performance.navigation?.type === 1;
}

// ─── Logika Tampil ────────────────────────────────────────

function shouldShowPopup() {
    if (isPWAInstalled()) return false;
    if (!isMobile())      return false;

    // Setiap reload/refresh → hapus dismiss sesi → tampilkan popup
    if (isPageReload()) {
        sessionStorage.removeItem(PWA_SESSION_KEY);
        return true;
    }

    // Navigasi ke halaman lain → jika sudah dismiss sesi ini, jangan tampilkan
    return sessionStorage.getItem(PWA_SESSION_KEY) !== 'true';
}

// ─── Tampil / Sembunyikan ─────────────────────────────────

function showInstallPopup() {
    const popup = document.getElementById('pwa-install-popup');
    if (!popup) return;

    popup.classList.remove('hidden');
    popup.classList.add('flex');

    const androidContent = document.getElementById('pwa-android-content');
    const iosContent     = document.getElementById('pwa-ios-content');
    if (!androidContent || !iosContent) return;

    if (isIOS()) {
        androidContent.classList.add('hidden');
        iosContent.classList.remove('hidden');
    } else {
        iosContent.classList.add('hidden');
        androidContent.classList.remove('hidden');

        // Jika deferredPrompt sudah tersedia, tampilkan tombol install
        // Jika belum tersedia (browser belum siap), tampilkan fallback
        updateAndroidButton();
    }
}

function hideInstallPopup() {
    const popup = document.getElementById('pwa-install-popup');
    if (!popup) return;
    popup.classList.add('hidden');
    popup.classList.remove('flex');
}

/**
 * Dismiss = simpan di sessionStorage saja (bukan localStorage).
 * sessionStorage otomatis reset setiap refresh / tab baru.
 */
function dismissPopup() {
    sessionStorage.setItem(PWA_SESSION_KEY, 'true');
    hideInstallPopup();
}

// ─── Tombol Install Android ───────────────────────────────

function updateAndroidButton() {
    const btn = document.getElementById('pwa-install-btn');
    if (!btn) return;

    if (deferredPrompt) {
        // Prompt tersedia → tampilkan tombol install aktif
        btn.disabled = false;
        btn.className = 'btn-primary w-full mb-3 py-3 flex items-center justify-center';
        btn.innerHTML = `
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Instal Aplikasi
        `;
    } else {
        // Prompt belum siap → tampilkan panduan singkat via menu browser
        btn.disabled = true;
        btn.className = 'w-full mb-3 py-2 text-sm text-gray-400 text-center cursor-default';
        btn.innerHTML = 'Membuka dialog instalasi&hellip;';
    }
}

// ─── Event: Tangkap prompt native Android ────────────────

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;

    // Jika popup sudah tampil (tombol masih disabled), aktifkan tombolnya
    const popup = document.getElementById('pwa-install-popup');
    if (popup && !popup.classList.contains('hidden')) {
        updateAndroidButton();
    }
});

// ─── Event: Berhasil diinstal ─────────────────────────────

window.addEventListener('appinstalled', () => {
    localStorage.setItem(PWA_INSTALLED_KEY, 'true');
    deferredPrompt = null;
    hideInstallPopup();
});

// ─── DOMContentLoaded ─────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {

    if (shouldShowPopup()) {
        setTimeout(showInstallPopup, 1500);
    }

    // Tombol dismiss
    document.getElementById('pwa-dismiss-btn')
        ?.addEventListener('click', dismissPopup);
    document.getElementById('pwa-dismiss-btn-2')
        ?.addEventListener('click', dismissPopup);

    // Tombol install Android → trigger native dialog
    document.getElementById('pwa-install-btn')
        ?.addEventListener('click', async () => {
            if (!deferredPrompt) return;

            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;

            if (outcome === 'accepted') {
                localStorage.setItem(PWA_INSTALLED_KEY, 'true');
            }

            deferredPrompt = null;
            hideInstallPopup();
        });

    // Tutup popup bila klik overlay (luar card)
    document.getElementById('pwa-install-popup')
        ?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) dismissPopup();
        });
});

// ─── Ekspor global ────────────────────────────────────────
window.masjidNusaPWA = { dismissPopup, showInstallPopup };
