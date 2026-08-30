<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-branding-meta title="Daftar Akun" :suffix="$brand->appName()" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-branding-styles />
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-700 flex items-center justify-center p-4 py-8">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}">
                <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->appName() }}" class="h-14 w-auto mx-auto mb-3 drop-shadow-lg">
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $brand->appName() }}</h1>
            <p class="text-emerald-300 text-sm">Daftar Akun Jamaah</p>
        </div>

        <div class="bg-white rounded-3xl shadow-2xl p-8" x-data="{ showForm: {{ ($googleEnabled ?? false) ? 'false' : 'true' }} }">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Buat Akun</h2>
            <p class="text-sm text-gray-500 mb-6">Daftar cepat dengan Gmail — tanpa perlu buat password</p>

            {{-- Google Register (recommended) --}}
            <x-google-auth-button mode="register" :google-enabled="$googleEnabled ?? false" />

            @if($googleEnabled ?? false)
            <button type="button" @click="showForm = !showForm"
                class="w-full text-sm text-emerald-700 hover:text-emerald-800 font-medium py-2 mb-4 flex items-center justify-center gap-1">
                <span x-text="showForm ? 'Sembunyikan form manual' : 'Daftar manual dengan email'"></span>
                <svg class="w-4 h-4 transition-transform" :class="showForm && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            @endif

            <div x-show="showForm" x-cloak>
                @if($googleEnabled ?? false)
                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center text-xs"><span class="bg-white px-2 text-gray-400">form manual</span></div>
                </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-input @error('name') border-red-500 @enderror" placeholder="Nama lengkap Anda" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') border-red-500 @enderror" placeholder="email@example.com" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">No. HP (Opsional)</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>

                    <div>
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input @error('password') border-red-500 @enderror" placeholder="Minimal 8 karakter" required>
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password" required>
                    </div>

                    <button type="submit" class="btn-primary w-full py-3 text-base">Buat Akun Manual</button>
                </form>
            </div>

            <p class="text-center text-sm text-gray-600 mt-6">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-semibold">Masuk</a>
            </p>

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
