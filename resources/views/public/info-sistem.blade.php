@extends('layouts.public')

@section('title', 'Info Sistem')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6">
    <h1 class="text-xl font-bold text-gray-900 mb-5">Info Sistem</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="card p-5">
            <div class="flex items-center gap-3 mb-4">
                <img src="{{ $brand->logoIconUrl() }}" alt="{{ $brand->appName() }}" class="h-12 w-auto">
                <div>
                    <h2 class="font-bold text-gray-900">{{ $brand->appName() }}</h2>
                    <p class="text-sm text-gray-500">{{ $brand->mosqueName() }}</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">
                Aplikasi digital masjid untuk informasi kegiatan, dakwah, donasi online, jadwal sholat, dan layanan umat.
            </p>
        </div>

        <div class="card p-5 bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-100">
            <h2 class="font-bold text-gray-900 mb-3">Platform {{ config('platform.name') }}</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Versi Platform</dt>
                    <dd class="font-semibold text-gray-900">{{ config('platform.name') }} v{{ config('platform.version') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Pengembang</dt>
                    <dd>
                        <a href="{{ config('platform.developer_url') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-700 hover:underline">
                            {{ config('platform.developer') }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Hak Cipta Platform</dt>
                    <dd class="font-semibold text-gray-900">{{ config('platform.copyright') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="card p-5 mt-5">
        <h2 class="font-bold text-gray-900 mb-2">Ingin Sistem Serupa?</h2>
        <p class="text-sm text-gray-600 leading-relaxed mb-4">
            MasjidNusa adalah platform manajemen masjid digital yang dikembangkan oleh
            <a href="{{ config('platform.developer_url') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-700 hover:underline">{{ config('platform.developer') }}</a>.
            Jika masjid Anda membutuhkan sistem serupa, silakan kunjungi website pengembang untuk informasi lebih lanjut.
        </p>
        <a href="{{ config('platform.developer_url') }}" target="_blank" rel="noopener noreferrer" class="btn-primary inline-flex px-5 py-2.5 text-sm">
            Kunjungi {{ config('platform.developer') }}
        </a>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        &copy; {{ config('platform.copyright') }}. Hak cipta dilindungi undang-undang.
    </p>
</div>
@endsection
