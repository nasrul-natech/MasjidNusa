<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-branding-meta :title="trim($__env->yieldContent('title')) ?: 'Error'" :suffix="$brand->appName()" />
    @vite(['resources/css/app.css'])
    <x-branding-styles />
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-md w-full">
        <div class="mb-6">
            <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->appName() }}" class="h-16 mx-auto">
        </div>

        <div class="text-8xl font-bold text-emerald-600 mb-4">@yield('code')</div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">@yield('title')</h1>
        <p class="text-gray-500 mb-8">@yield('message')</p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url('/') }}" class="btn-primary px-6 py-3">Kembali ke Beranda</a>
            <button onclick="history.back()" class="btn-secondary px-6 py-3">Kembali</button>
        </div>
    </div>
</body>
</html>
