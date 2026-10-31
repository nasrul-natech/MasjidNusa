<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ auth()->id() }}">
    @endauth

    {{-- VAPID Public Key for Push --}}
    @if(config('webpush.vapid.public_key'))
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    @endif

    <x-branding-meta
        :title="trim($__env->yieldContent('title')) ?: $brand->appName()"
        :description="trim($__env->yieldContent('meta_description')) ?: null"
    />

    {{-- Capture install eligibility before deferred Vite modules execute. --}}
    <script>
    window.__pwaPrompt = null;
    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        window.__pwaPrompt = event;
        window.dispatchEvent(new Event('pwa-install-ready'));
    });
    </script>

    {{-- PWA --}}
    <link rel="manifest" href="{{ route('manifest') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">

    {{-- Base URL for JS --}}
    <meta name="base-url" content="{{ rtrim(config('app.url'), '/') }}/">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Amiri+Quran&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-branding-styles />
    @stack('styles')
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 min-h-screen antialiased" x-data="{ mobileMenuOpen: false }">

    {{-- PWA Install Popup --}}
    @include('components.pwa-install-popup')

    {{-- Popup Ad --}}
    @include('components.popup-ad')

    {{-- Top Header --}}
    <header class="sticky top-0 z-40 bg-white border-b border-gray-100 safe-top shadow-sm relative">
        <div class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-emerald-600 via-teal-500 to-emerald-600" aria-hidden="true"></div>
        <div class="max-w-7xl mx-auto px-4 lg:px-6">
            <div class="flex items-center justify-between h-14 lg:h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ $brand->logoIconUrl() }}" alt="{{ $brand->appName() }}" class="h-9 w-auto">
                    <div class="hidden sm:block">
                        <p class="text-sm font-bold text-emerald-800 leading-tight">{{ $brand->appName() }}</p>
                        <p class="text-xs text-gray-500 leading-tight">{{ $brand->tagline() }}</p>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('home') ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 hover:text-emerald-700 hover:bg-gray-50' }}">
                        @include('components.icons.home', ['class' => 'w-4 h-4']) Beranda
                    </a>
                    <a href="{{ route('quran.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('quran.*') ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 hover:text-emerald-700 hover:bg-gray-50' }}">
                        @include('components.icons.quran', ['class' => 'w-4 h-4']) Al-Quran
                    </a>
                    <a href="{{ route('doa.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('doa.*') ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 hover:text-emerald-700 hover:bg-gray-50' }}">
                        @include('components.icons.doa', ['class' => 'w-4 h-4']) Doa
                    </a>
                    <a href="{{ route('kegiatan.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('kegiatan.*') ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 hover:text-emerald-700 hover:bg-gray-50' }}">
                        @include('components.icons.calendar', ['class' => 'w-4 h-4']) Kegiatan
                    </a>
                    <a href="{{ route('dakwah.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('dakwah.*') ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 hover:text-emerald-700 hover:bg-gray-50' }}">
                        @include('components.icons.book', ['class' => 'w-4 h-4']) Dakwah
                    </a>
                    <a href="{{ route('donasi.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('donasi.*') ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 hover:text-emerald-700 hover:bg-gray-50' }}">
                        @include('components.icons.donation', ['class' => 'w-4 h-4']) Donasi
                    </a>
                    <a href="{{ route('info-masjid.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('info-masjid.*') ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 hover:text-emerald-700 hover:bg-gray-50' }}">
                        @include('components.icons.building', ['class' => 'w-4 h-4']) Info
                    </a>
                </nav>

                {{-- Right Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Notification Bell (visible for all) --}}
                    @include('components.notification-dropdown')

                    {{-- Profile / Login --}}
                    @include('components.profile-dropdown')
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="pb-20 md:pb-6">
        @yield('content')
    </main>

    {{-- Mobile Bottom Navigation --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 safe-bottom shadow-[0_-4px_20px_rgba(0,0,0,0.06)]" x-data="{ moreOpen: false }">
        <div class="flex items-end">
            <x-bottom-nav-item href="{{ route('home') }}" icon="home" label="Home" :active="request()->routeIs('home')" />
            <x-bottom-nav-item href="{{ route('quran.index') }}" icon="quran" label="Quran" :active="request()->routeIs('quran.*')" />
            <x-bottom-nav-item href="{{ route('donasi.index') }}" icon="donation" label="Donasi" :active="request()->routeIs('donasi.*')" featured />
            <x-bottom-nav-item href="{{ route('kegiatan.index') }}" icon="calendar" label="Kegiatan" :active="request()->routeIs('kegiatan.*')" />
            <x-bottom-nav-item type="button" icon="menu" label="Menu" :active="false" @click="moreOpen = true" />
        </div>

        {{-- More Menu Sheet --}}
        <div x-show="moreOpen" x-cloak class="fixed inset-0 z-50" @keydown.escape.window="moreOpen = false">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="moreOpen = false" x-show="moreOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"></div>
            <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-2xl safe-bottom p-5"
                x-show="moreOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0">
                <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-4"></div>
                <h3 class="text-sm font-bold text-gray-900 mb-4">Menu Lainnya</h3>
                <div class="grid grid-cols-4 gap-3">
                    <x-quick-action href="{{ route('doa.index') }}" icon="doa" label="Doa" color="teal" :active="request()->routeIs('doa.*')" />
                    <x-quick-action href="{{ route('dakwah.index') }}" icon="book" label="Dakwah" color="amber" :active="request()->routeIs('dakwah.*')" />
                    <x-quick-action href="{{ route('info-masjid.index') }}" icon="building" label="Info" color="emerald" :active="request()->routeIs('info-masjid.*')" />
                    <x-quick-action href="{{ route('jadwal-sholat') }}" icon="prayer" label="Sholat" color="purple" :active="request()->routeIs('jadwal-sholat*')" />
                    <x-quick-action href="{{ route('home') }}#kegiatan" icon="calendar" label="Agenda" color="blue" />
                </div>
                <button @click="moreOpen = false" class="w-full mt-4 py-2.5 text-sm font-semibold text-gray-500 bg-gray-100 rounded-xl">Tutup</button>
            </div>
        </div>
    </nav>

    {{-- Footer (desktop only) --}}
    <footer class="hidden md:block bg-primary text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->appName() }}" class="h-12 w-auto brightness-200">
                        <div>
                            <h3 class="font-bold text-lg">{{ $brand->appName() }}</h3>
                            <p class="text-emerald-300 text-sm">{{ $brand->tagline() }}</p>
                        </div>
                    </div>
                    <p class="text-emerald-200 text-sm leading-relaxed">Aplikasi digital untuk informasi masjid, kegiatan, dakwah, dan donasi online.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-emerald-100">Menu</h4>
                    <ul class="space-y-2 text-sm text-emerald-300">
                        <li><a href="{{ route('quran.index') }}" class="hover:text-white inline-flex items-center gap-1.5">@include('components.icons.quran', ['class' => 'w-3.5 h-3.5']) Al-Quran</a></li>
                        <li><a href="{{ route('doa.index') }}" class="hover:text-white inline-flex items-center gap-1.5">@include('components.icons.doa', ['class' => 'w-3.5 h-3.5']) Kumpulan Doa</a></li>
                        <li><a href="{{ route('kegiatan.index') }}" class="hover:text-white inline-flex items-center gap-1.5">@include('components.icons.calendar', ['class' => 'w-3.5 h-3.5']) Kegiatan</a></li>
                        <li><a href="{{ route('dakwah.index') }}" class="hover:text-white inline-flex items-center gap-1.5">@include('components.icons.book', ['class' => 'w-3.5 h-3.5']) Dakwah & Artikel</a></li>
                        <li><a href="{{ route('donasi.index') }}" class="hover:text-white inline-flex items-center gap-1.5">@include('components.icons.donation', ['class' => 'w-3.5 h-3.5']) Donasi</a></li>
                        <li><a href="{{ route('info-masjid.index') }}" class="hover:text-white inline-flex items-center gap-1.5">@include('components.icons.building', ['class' => 'w-3.5 h-3.5']) Info Masjid</a></li>
                        <li><a href="{{ route('jadwal-sholat') }}" class="hover:text-white inline-flex items-center gap-1.5">@include('components.icons.prayer', ['class' => 'w-3.5 h-3.5']) Jadwal Sholat</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-emerald-100">Kontak</h4>
                    <div class="space-y-2 text-sm text-emerald-300">
                        @php $mosque = \App\Models\MosqueProfile::current(); @endphp
                        @if($mosque)
                        <p>{{ $mosque->address }}</p>
                        <p>{{ $mosque->phone }}</p>
                        <p>{{ $mosque->email }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="border-t border-emerald-700 mt-8 pt-6 text-center text-emerald-400 text-sm">
                <p>&copy; {{ date('Y') }} {{ $brand->mosqueName() }}. Semua hak dilindungi.</p>
                <x-platform-credit />
            </div>
        </div>
    </footer>

    {{-- Toast Container (floating, non-blocking) --}}
    <div id="toast-container" class="fixed top-16 right-4 z-[999] space-y-2 pointer-events-none" style="max-width: 320px; width: calc(100% - 2rem);"></div>

    {{-- Global System Modal --}}
    <div id="sys-modal-overlay"
        class="fixed inset-0 z-[999] flex items-end sm:items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display:none"
        onclick="if(event.target===this) hideSysModal()">
        <div id="sys-modal-box" class="bg-white rounded-3xl shadow-2xl w-full max-w-xs p-6 text-center transform transition-all">
            <div id="sys-modal-icon" class="text-5xl mb-3"></div>
            <h3 id="sys-modal-title" class="font-bold text-gray-900 text-base mb-2"></h3>
            <p id="sys-modal-body" class="text-gray-500 text-sm mb-5 leading-relaxed"></p>
            <div id="sys-modal-actions" class="flex gap-3 justify-center"></div>
        </div>
    </div>

    <style>
    @@keyframes toast-in {
        from { opacity: 0; transform: translateX(20px) scale(0.95); }
        to   { opacity: 1; transform: translateX(0) scale(1); }
    }
    .toast-item {
        animation: toast-in 0.25s ease-out forwards;
        pointer-events: auto;
    }
    </style>

    <script>
    // ===== Global System Popup & Toast =====
    function showToast(message, type = 'success', duration = 4000) {
        const icons = { success: '✓', error: '✕', warning: '!', info: 'i' };
        const colors = {
            success: { bg: 'var(--color-primary)', icon: 'var(--color-emerald-300)' },
            error:   { bg: '#991b1b', icon: '#f87171' },
            warning: { bg: 'var(--color-accent)', icon: '#fff' },
            info:    { bg: '#1e40af', icon: '#60a5fa' }
        };
        const c = colors[type] || colors.info;

        const el = document.createElement('div');
        el.className = 'toast-item';
        el.style.cssText = `
            display: flex; align-items: center; gap: 10px;
            padding: 12px 14px; border-radius: 14px;
            background: ${c.bg};
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            color: #fff; font-size: 13.5px; font-weight: 500;
            cursor: pointer; width: 100%;
        `;
        el.innerHTML = `
            <span style="
                width:22px; height:22px; border-radius:50%;
                background:rgba(255,255,255,0.2);
                display:flex; align-items:center; justify-content:center;
                font-size:12px; font-weight:700; flex-shrink:0; color:${c.icon};
            ">${icons[type] || 'i'}</span>
            <span style="flex:1; line-height:1.4;">${message}</span>
            <span style="opacity:0.6; font-size:16px; flex-shrink:0; line-height:1;">×</span>
        `;

        const container = document.getElementById('toast-container');
        container.appendChild(el);

        const dismiss = () => {
            el.style.transition = 'opacity 0.25s, transform 0.25s';
            el.style.opacity = '0';
            el.style.transform = 'translateX(20px)';
            setTimeout(() => el.remove(), 250);
        };
        el.addEventListener('click', dismiss);

        if (duration > 0) {
            setTimeout(dismiss, duration);
        }
    }

    // Show session flash messages as floating toasts
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            showToast(@json(session('success')), 'success');
        @endif
        @if(session('error'))
            showToast(@json(session('error')), 'error');
        @endif
        @if(session('warning'))
            showToast(@json(session('warning')), 'warning');
        @endif
        @if(session('info'))
            showToast(@json(session('info')), 'info');
        @endif
    });

    function showSysModal({ title = '', body = '', icon = '💬', type = 'info', confirmText = 'OK', cancelText = '', onConfirm = null, onCancel = null }) {
        document.getElementById('sys-modal-icon').textContent = icon;
        document.getElementById('sys-modal-title').textContent = title;
        document.getElementById('sys-modal-body').textContent = body;
        const actions = document.getElementById('sys-modal-actions');
        actions.innerHTML = '';
        if (cancelText) {
            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors';
            cancelBtn.textContent = cancelText;
            cancelBtn.onclick = () => { hideSysModal(); onCancel && onCancel(); };
            actions.appendChild(cancelBtn);
        }
        const confirmBtn = document.createElement('button');
        const btnColors = { success: 'bg-emerald-700 hover:bg-emerald-800', error: 'bg-red-600 hover:bg-red-700', warning: 'bg-amber-500 hover:bg-amber-600', info: 'bg-emerald-700 hover:bg-emerald-800' };
        confirmBtn.className = `flex-1 px-4 py-2.5 ${btnColors[type] || btnColors.info} text-white rounded-xl text-sm font-semibold transition-colors`;
        confirmBtn.textContent = confirmText;
        confirmBtn.onclick = () => { hideSysModal(); onConfirm && onConfirm(); };
        actions.appendChild(confirmBtn);
        const overlay = document.getElementById('sys-modal-overlay');
        overlay.style.display = 'flex';
        requestAnimationFrame(() => { document.getElementById('sys-modal-box').style.transform = 'scale(1)'; });
    }

    function hideSysModal() {
        document.getElementById('sys-modal-overlay').style.display = 'none';
    }
    </script>

    {{-- Adzan Client Timer --}}
    @include('components.adzan-client')

    @if(request()->boolean('logout'))
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistration().then(function (registration) {
            if (registration && registration.active) {
                registration.active.postMessage({ type: 'CLEAR_DYNAMIC_CACHE' });
            }
        });
    }
    </script>
    @endif

    @stack('scripts')
</body>
</html>
