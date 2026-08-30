<div
    id="pwa-install-popup"
    class="hidden fixed inset-0 z-50 items-end sm:items-center justify-center p-4 bg-black/40"
>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative animate-slide-up">

        {{-- Close button --}}
        <button id="pwa-dismiss-btn"
                class="absolute top-4 right-4 p-1.5 rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- App icon + info --}}
        <div class="flex items-center gap-4 mb-5">
            <img src="{{ $brand->logoIconUrl() }}" alt="{{ $brand->appName() }}"
                 class="w-16 h-16 rounded-2xl shadow-md flex-shrink-0 object-cover"
                 onerror="this.src='{{ asset('images/logo-masjidnusa-icon.png') }}'">
            <div>
                <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ $brand->appName() }}</h3>
                <p class="text-sm text-gray-500">{{ $brand->tagline() }}</p>
                <div class="flex gap-0.5 mt-1">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-600 mb-5 leading-relaxed">
            Instal aplikasi untuk akses cepat, notifikasi kegiatan masjid, dan pengalaman lebih nyaman di HP Anda.
        </p>

        {{-- ── ANDROID: tombol install langsung ── --}}
        <div id="pwa-android-content">
            <button id="pwa-install-btn" type="button"
                    class="btn-primary w-full mb-3 py-3 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Instal Aplikasi
            </button>
            <div id="pwa-android-fallback" class="hidden bg-gray-50 border border-gray-100 rounded-xl p-4 mb-3">
                <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Instal via browser:</p>
                <p class="text-sm text-gray-600">Tap menu <strong>⋮</strong> di kanan atas → pilih <strong>"Install app"</strong> atau <strong>"Add to Home screen"</strong>.</p>
            </div>
        </div>

        {{-- ── iOS: panduan Add to Home Screen (satu-satunya cara di Safari iOS) ── --}}
        <div id="pwa-ios-content" class="hidden">
            <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 space-y-3">
                <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wide">
                    Tambahkan ke Home Screen:
                </p>
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 bg-emerald-600 rounded-full flex items-center justify-center
                                 text-xs font-bold text-white flex-shrink-0">1</span>
                    <p class="text-sm text-gray-700">
                        Tap ikon <strong>Bagikan</strong>
                        <svg class="inline w-4 h-4 mb-0.5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l-3 3h2v7h2V5h2L12 2zm-7 9v9h14v-9h-2v7H7v-7H5z"/>
                        </svg>
                        di bawah Safari
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 bg-emerald-600 rounded-full flex items-center justify-center
                                 text-xs font-bold text-white flex-shrink-0">2</span>
                    <p class="text-sm text-gray-700">Pilih <strong>"Add to Home Screen"</strong></p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 bg-emerald-600 rounded-full flex items-center justify-center
                                 text-xs font-bold text-white flex-shrink-0">3</span>
                    <p class="text-sm text-gray-700">Tap <strong>"Add"</strong> di pojok kanan atas</p>
                </div>
            </div>
        </div>

        <button id="pwa-dismiss-btn-2" type="button"
                class="w-full mt-3 text-sm text-gray-400 hover:text-gray-600 py-2 transition-colors">
            Nanti saja
        </button>
    </div>
</div>

<style>
@keyframes slide-up {
    from { transform: translateY(24px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
.animate-slide-up { animation: slide-up 0.3s ease-out both; }
</style>
