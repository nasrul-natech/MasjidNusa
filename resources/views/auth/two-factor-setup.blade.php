<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-branding-meta title="Aktivasi 2FA" :suffix="$brand->appName()" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-branding-styles />
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-900 via-emerald-800 to-emerald-700 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <img src="{{ $brand->logoIconUrl() }}" alt="{{ $brand->appName() }}" class="h-14 w-auto mx-auto mb-3">
            <h1 class="text-xl font-bold text-white">Aktivasi 2FA Wajib</h1>
            <p class="text-emerald-300 text-sm mt-1">Keamanan tambahan untuk akun admin (ISO 27001)</p>
        </div>

        <div class="bg-white rounded-3xl shadow-2xl p-8">
            @if(session('info'))
                <x-alert type="info" :message="session('info')" class="mb-4" />
            @endif

            <div class="mb-6">
                <h2 class="font-semibold text-gray-900 mb-2">Langkah 1: Scan QR Code</h2>
                <p class="text-sm text-gray-500 mb-4">Gunakan Google Authenticator, Authy, atau Microsoft Authenticator.</p>
                <div class="flex justify-center bg-gray-50 rounded-xl p-4 border border-gray-100">
                    {!! $qrCode !!}
                </div>
                <p class="text-xs text-gray-400 mt-3 text-center font-mono break-all">
                    Manual: {{ $secret }}
                </p>
            </div>

            <form method="POST" action="{{ route('two-factor.setup') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Langkah 2: Masukkan kode verifikasi</label>
                    <input
                        type="text"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        class="form-input text-center text-2xl tracking-widest font-mono @error('code') border-red-500 @enderror"
                        placeholder="000000"
                        required>
                    @error('code') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary w-full py-3">Aktifkan 2FA & Masuk</button>
            </form>

            <p class="text-xs text-gray-400 mt-4 text-center">
                Setelah aktivasi, Anda akan menerima kode pemulihan sekali pakai. Simpan di tempat aman.
            </p>
        </div>
    </div>
</body>
</html>
