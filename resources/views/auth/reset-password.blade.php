<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-branding-meta title="Reset Password" :suffix="$brand->appName()" />
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
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-input @error('password') border-red-500 @enderror" placeholder="Minimal 8 karakter" required>
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-input" required>
                </div>

                <button type="submit" class="btn-primary w-full py-3">Reset Password</button>
            </form>
        </div>
    </div>
</body>
</html>
