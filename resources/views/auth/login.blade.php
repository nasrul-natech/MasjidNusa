<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-branding-meta title="Login" :suffix="$brand->appName()" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-branding-styles />
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-700 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}">
                <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->appName() }}" class="h-16 w-auto mx-auto mb-3 drop-shadow-lg">
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $brand->appName() }}</h1>
            <p class="text-emerald-300 text-sm">{{ $brand->tagline() }}</p>
        </div>

        <div class="bg-white rounded-3xl shadow-2xl p-8" x-data="{ showPass: false, showDemo: false, showEmailForm: {{ ($googleEnabled ?? false) ? 'false' : 'true' }} }">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Masuk ke Akun</h2>
            <p class="text-sm text-gray-500 mb-6">Pilih cara masuk yang paling mudah untuk Anda</p>

            @if(session('error'))
                <x-alert type="danger" :message="session('error')" class="mb-4" />
            @endif
            @if(session('success'))
                <x-alert type="success" :message="session('success')" class="mb-4" />
            @endif
            @if(session('recovery_codes'))
                <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <p class="text-sm font-semibold text-amber-800 mb-2">Simpan kode pemulihan 2FA ini:</p>
                    <div class="grid grid-cols-2 gap-1 font-mono text-xs text-amber-900">
                        @foreach(session('recovery_codes') as $code)
                            <span>{{ $code }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Google Login (Jamaah - tanpa password) --}}
            <x-google-auth-button mode="login" :redirect="$redirect ?? '/'" :google-enabled="$googleEnabled ?? false" />

            @if($googleEnabled ?? false)
            <button type="button" @click="showEmailForm = !showEmailForm"
                class="w-full text-sm text-emerald-700 hover:text-emerald-800 font-medium py-2 mb-4 flex items-center justify-center gap-1">
                <span x-text="showEmailForm ? 'Sembunyikan login email' : 'Masuk dengan email & password'"></span>
                <svg class="w-4 h-4 transition-transform" :class="showEmailForm && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            @endif

            <div x-show="showEmailForm" x-cloak>
                @if($googleEnabled ?? false)
                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center text-xs"><span class="bg-white px-2 text-gray-400">login admin / email</span></div>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect ?? '/' }}">

                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="login-email" value="{{ old('email') }}"
                            class="form-input @error('email') border-red-500 @enderror"
                            placeholder="email@example.com" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="form-label mb-0">Password</label>
                            <a href="{{ route('password.request') }}" class="text-xs text-emerald-600 hover:text-emerald-700">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password" id="login-password"
                                class="form-input pr-11 @error('password') border-red-500 @enderror"
                                placeholder="••••••••" required>
                            <button type="button" @click="showPass = !showPass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" checked
                            class="w-4 h-4 rounded text-emerald-600 border-gray-300 focus:ring-emerald-500">
                        <span class="text-sm text-gray-600">Ingat saya <span class="text-xs text-gray-400">(30 hari)</span></span>
                    </label>

                    <button type="submit" class="btn-primary w-full py-3 text-base">Masuk</button>
                </form>
            </div>

            <p class="text-center text-sm text-gray-600 mt-6">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-emerald-600 hover:text-emerald-700 font-semibold">Daftar sekarang</a>
            </p>

            @if(!empty($demoAccounts))
            <div class="mt-6 pt-4 border-t border-gray-100">
                <button type="button" @click="showDemo = !showDemo"
                    class="flex items-center justify-between w-full text-sm text-amber-600 hover:text-amber-700 font-medium">
                    <span>Akun Demo (Dev Only)</span>
                    <svg class="w-4 h-4 transition-transform" :class="showDemo && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="showDemo" x-cloak class="mt-3 space-y-2">
                    @foreach($demoAccounts as $account)
                    @php
                        $roleColors = ['purple'=>'text-purple-700','blue'=>'text-blue-700','amber'=>'text-amber-700','cyan'=>'text-cyan-700','emerald'=>'text-emerald-700'];
                        $colorClass = $roleColors[$account['color']] ?? 'text-gray-700';
                    @endphp
                    <button type="button"
                        @click="showEmailForm = true; document.getElementById('login-email').value='{{ $account['email'] }}'; document.getElementById('login-password').value='{{ $account['password'] }}';"
                        class="w-full text-left px-3 py-2 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors">
                        <span class="text-xs font-semibold {{ $colorClass }}">{{ $account['role'] }}</span>
                        <span class="block text-xs text-gray-500 font-mono">{{ $account['email'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    <style>[x-cloak]{display:none!important}</style>
</body>
</html>
