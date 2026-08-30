<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ rtrim(config('app.url'), '/') }}/">
    @auth<meta name="user-id" content="{{ auth()->id() }}">@endauth
    <title>@yield('title', 'Panel Admin') - {{ $brand->appName() }}</title>
    <link rel="icon" type="image/png" href="{{ $brand->faviconUrl() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-branding-styles />
    @stack('styles')
</head>
<body class="bg-gray-50 antialiased" x-data="{ sidebar: false }" @keydown.escape="sidebar = false">

    <div class="flex h-screen overflow-hidden">

        {{-- Mobile Backdrop Overlay --}}
        <div
            x-show="sidebar"
            x-cloak
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebar = false"
            class="lg:hidden fixed inset-0 z-20 bg-black/50 backdrop-blur-sm"
        ></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebar ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 flex flex-col transition-transform duration-300 ease-in-out"
        >
            {{-- Sidebar Header --}}
            <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-100">
                <img src="{{ $brand->logoIconUrl() }}" alt="{{ $brand->appName() }}" class="h-8 w-auto shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-emerald-800 leading-tight truncate">{{ $brand->appName() }}</p>
                    <p class="text-xs text-gray-500 leading-tight truncate">@yield('panel-name', 'Admin Panel')</p>
                </div>
                {{-- Close button - mobile only --}}
                <button @click="sidebar = false" class="lg:hidden p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
                @yield('sidebar-menu')
            </nav>

            {{-- Sidebar Footer --}}
            <div class="border-t border-gray-100 px-4 py-3">
                <div class="flex items-center gap-2.5 mb-3">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-full object-cover shrink-0" alt="Avatar">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->getRoleNames()->first() }}</p>
                    </div>
                </div>
                <div class="flex gap-1">
                    <a href="{{ route('home') }}" class="flex-1 flex items-center justify-center gap-1.5 text-xs text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg py-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Publik
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-1.5 text-xs text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg py-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top Bar --}}
            <header class="bg-white border-b border-gray-200 px-4 py-2.5 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <button @click="sidebar = !sidebar"
                        class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors"
                        :aria-label="sidebar ? 'Tutup menu' : 'Buka menu'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-sm font-semibold text-gray-900 leading-tight">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-xs text-gray-400 hidden sm:block leading-tight">@yield('page-subtitle', '')</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @include('components.notification-dropdown')
                    <div class="hidden sm:flex items-center gap-2">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-7 h-7 rounded-full object-cover" alt="Avatar">
                        <span class="text-xs font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <div class="max-w-7xl mx-auto w-full">
                @if(session('recovery_codes'))
                    <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                        <p class="text-sm font-semibold text-amber-800 mb-2">Simpan kode pemulihan 2FA — hanya ditampilkan sekali:</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1 font-mono text-xs text-amber-900 mb-2">
                            @foreach(session('recovery_codes') as $code)
                                <span class="bg-white px-2 py-1 rounded">{{ $code }}</span>
                            @endforeach
                        </div>
                        <p class="text-xs text-amber-600">Gunakan kode ini jika kehilangan akses ke authenticator.</p>
                    </div>
                @endif
                @if(session('success'))
                    <x-alert type="success" :message="session('success')" dismissible class="mb-4" />
                @endif
                @if(session('error'))
                    <x-alert type="danger" :message="session('error')" dismissible class="mb-4" />
                @endif

                @yield('content')
                </div>
            </main>
        </div>
    </div>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed bottom-6 right-4 z-50 space-y-2 max-w-xs w-full pointer-events-none px-4"></div>

    {{-- Global System Modal --}}
    <div id="sys-modal-overlay"
        class="fixed inset-0 z-[999] flex items-end sm:items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display:none"
        onclick="if(event.target===this) hideSysModal()">
        <div id="sys-modal-box" class="bg-white rounded-3xl shadow-2xl w-full max-w-xs p-6 text-center">
            <div id="sys-modal-icon" class="text-5xl mb-3"></div>
            <h3 id="sys-modal-title" class="font-bold text-gray-900 text-base mb-2"></h3>
            <p id="sys-modal-body" class="text-gray-500 text-sm mb-5 leading-relaxed"></p>
            <div id="sys-modal-actions" class="flex gap-3 justify-center"></div>
        </div>
    </div>

    <script>
    function showToast(message, type = 'success') {
        const icons = { success: '✓', error: '✕', warning: '!', info: 'i' };
        const colors = { success: 'bg-emerald-700', error: 'bg-red-600', warning: 'bg-amber-500', info: 'bg-blue-600' };
        const el = document.createElement('div');
        el.className = `toast-item flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-lg text-white text-sm font-medium w-full pointer-events-auto ${colors[type] || colors.info}`;
        el.style.cssText = 'animation: toast-in 0.25s ease-out; cursor:pointer;';
        el.innerHTML = `<span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold shrink-0">${icons[type]||'i'}</span><span class="flex-1">${message}</span>`;
        el.onclick = () => { el.style.opacity = '0'; el.style.transition = 'opacity 0.2s'; setTimeout(() => el.remove(), 200); };
        document.getElementById('toast-container').appendChild(el);
        setTimeout(() => { if (el.parentNode) { el.style.opacity = '0'; el.style.transition = 'opacity 0.3s'; setTimeout(() => el.remove(), 300); } }, 3500);
    }
    function showSysModal({ title='', body='', icon='💬', type='info', confirmText='OK', cancelText='', onConfirm=null, onCancel=null }) {
        document.getElementById('sys-modal-icon').textContent = icon;
        document.getElementById('sys-modal-title').textContent = title;
        document.getElementById('sys-modal-body').textContent = body;
        const actions = document.getElementById('sys-modal-actions');
        actions.innerHTML = '';
        if (cancelText) { const b = document.createElement('button'); b.className = 'flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50'; b.textContent = cancelText; b.onclick = () => { hideSysModal(); onCancel && onCancel(); }; actions.appendChild(b); }
        const c = document.createElement('button'); const bc = { success:'bg-emerald-700 hover:bg-emerald-800', error:'bg-red-600 hover:bg-red-700', warning:'bg-amber-500 hover:bg-amber-600', info:'bg-emerald-700 hover:bg-emerald-800' };
        c.className = `flex-1 px-4 py-2.5 ${bc[type]||bc.info} text-white rounded-xl text-sm font-semibold`; c.textContent = confirmText; c.onclick = () => { hideSysModal(); onConfirm && onConfirm(); }; actions.appendChild(c);
        document.getElementById('sys-modal-overlay').style.display = 'flex';
    }
    function hideSysModal() { document.getElementById('sys-modal-overlay').style.display = 'none'; }
    </script>

    <style>
    @keyframes toast-in { from { opacity:0; transform:translateX(12px); } to { opacity:1; transform:translateX(0); } }
    [x-cloak] { display: none !important; }
    </style>

    @stack('scripts')
</body>
</html>
