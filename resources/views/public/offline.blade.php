<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <x-branding-meta title="Offline" :suffix="$brand->appName()" />
    <x-branding-styles />
    <style>
        body { font-family: system-ui; background: #f9fafb; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .container { text-align: center; padding: 2rem; max-width: 400px; }
        .icon { font-size: 4rem; margin-bottom: 1rem; }
        h1 { color: #1f2937; font-size: 1.5rem; margin-bottom: 0.5rem; }
        p { color: #6b7280; font-size: 0.875rem; margin-bottom: 1.5rem; }
        button { background: var(--color-primary, #064e3b); color: white; border: none; padding: 0.75rem 2rem; border-radius: 0.75rem; font-size: 0.875rem; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">📵</div>
        <h1>Sedang Offline</h1>
        <p>Anda tidak terhubung ke internet. Silakan periksa koneksi Anda dan coba lagi.</p>
        <button onclick="window.location.reload()">Coba Lagi</button>
    </div>
</body>
</html>
