<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-branding-meta title="Lupa Password" :suffix="$brand->appName()" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-branding-styles />
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-900 to-emerald-700 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->appName() }}" class="h-14 w-auto mx-auto mb-3 drop-shadow-lg">
            <h1 class="text-2xl font-bold text-white">Reset Password</h1>
        </div>
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            @if(session('success'))
                <x-alert type="success" :message="session('success')" class="mb-4" />
            @endif

            <p class="text-sm text-gray-600 mb-6">Masukkan email Anda dan kami akan mengirimkan link untuk reset password.</p>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input @error('email') border-red-500 @enderror" placeholder="email@example.com" required>
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary w-full py-3">Kirim Link Reset</button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm text-emerald-600 hover:text-emerald-700">← Kembali ke Login</a>
            </div>
        </div>
    </div>
</body>
</html>
