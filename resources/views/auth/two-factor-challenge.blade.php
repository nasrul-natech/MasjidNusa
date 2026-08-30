<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-branding-meta title="Verifikasi 2FA" :suffix="$brand->appName()" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-branding-styles />
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-700 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-6">
            <img src="{{ $brand->logoIconUrl() }}" alt="{{ $brand->appName() }}" class="h-14 w-auto mx-auto mb-3">
            <h1 class="text-xl font-bold text-white">Verifikasi 2 Langkah</h1>
            <p class="text-emerald-300 text-sm mt-1">Masukkan kode dari aplikasi authenticator</p>
        </div>

        <div class="bg-white rounded-3xl shadow-2xl p-8">
            @if(session('info'))
                <x-alert type="info" :message="session('info')" class="mb-4" />
            @endif

            <form method="POST" action="{{ route('two-factor.challenge') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="form-label">Kode Authenticator (6 digit)</label>
                    <input
                        type="text"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        class="form-input text-center text-2xl tracking-widest font-mono @error('code') border-red-500 @enderror"
                        placeholder="000000"
                        autofocus
                        required>
                    @error('code') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary w-full py-3">Verifikasi</button>
            </form>

            <details class="mt-6">
                <summary class="text-sm text-gray-500 cursor-pointer hover:text-gray-700">Gunakan kode pemulihan</summary>
                <form method="POST" action="{{ route('two-factor.challenge') }}" class="mt-3 space-y-3">
                    @csrf
                    <input type="text" name="code" class="form-input font-mono text-sm" placeholder="XXXX-XXXX" required>
                    <button type="submit" class="btn-secondary w-full py-2 text-sm">Verifikasi dengan Kode Pemulihan</button>
                </form>
            </details>

            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700">Kembali ke login</a>
            </div>
        </div>
    </div>
</body>
</html>
