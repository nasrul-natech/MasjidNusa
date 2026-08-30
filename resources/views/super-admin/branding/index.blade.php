@extends('layouts.super-admin')

@section('title', 'Branding')
@section('page-title', 'Branding & Identitas Masjid')

@section('content')
<x-admin.form-layout>
    <x-slot>
        <form action="{{ route('super-admin.branding.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="card p-5 lg:p-6 space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                        @include('components.icons.paint', ['class' => 'w-5 h-5 text-purple-700'])
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Identitas Aplikasi</h3>
                        <p class="text-xs text-gray-500">Nama, tagline, dan warna brand masjid</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nama Aplikasi</label>
                        <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? $brand->appName()) }}" class="form-input" required>
                        @error('app_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Nama Masjid</label>
                        <input type="text" name="mosque_name" value="{{ old('mosque_name', $settings['mosque_name'] ?? $brand->mosqueName()) }}" class="form-input" required>
                        @error('mosque_name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Tagline / Moto</label>
                        <input type="text" name="app_tagline" value="{{ old('app_tagline', $settings['app_tagline'] ?? $brand->tagline()) }}" class="form-input" placeholder="Menghubungkan Masjid, Umat & Kebaikan">
                        @error('app_tagline')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Warna Utama (Primary)</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="primary_color" value="{{ old('primary_color', $settings['primary_color'] ?? $brand->primaryColor()) }}" class="h-10 w-16 rounded-lg border border-gray-200 cursor-pointer">
                            <input type="text" value="{{ old('primary_color', $settings['primary_color'] ?? $brand->primaryColor()) }}" class="form-input flex-1 font-mono text-sm" readonly>
                        </div>
                        @error('primary_color')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Warna Aksen</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="accent_color" value="{{ old('accent_color', $settings['accent_color'] ?? $brand->accentColor()) }}" class="h-10 w-16 rounded-lg border border-gray-200 cursor-pointer">
                            <input type="text" value="{{ old('accent_color', $settings['accent_color'] ?? $brand->accentColor()) }}" class="form-input flex-1 font-mono text-sm" readonly>
                        </div>
                        @error('accent_color')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card p-5 lg:p-6 space-y-4">
                <h3 class="font-semibold text-gray-800">Media & Logo</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Logo Utama</label>
                        <img src="{{ $brand->logoUrl() }}" class="h-14 mb-2 object-contain">
                        <input type="file" name="logo" accept="image/*" class="form-input">
                        @error('logo')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Logo Icon</label>
                        <img src="{{ $brand->logoIconUrl() }}" class="h-14 mb-2 object-contain">
                        <input type="file" name="logo_icon" accept="image/*" class="form-input">
                        @error('logo_icon')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Favicon</label>
                        <div class="h-14 mb-2 flex items-center">
                            <img src="{{ $brand->faviconUrl() }}" class="h-8 w-8 object-contain" alt="Favicon">
                        </div>
                        <input type="file" name="favicon" accept="image/*" class="form-input">
                        @error('favicon')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary px-6">Simpan Branding</button>
        </form>
    </x-slot>

    <x-slot:aside>
        <div class="card p-5 bg-gradient-to-br from-purple-50 to-indigo-50 border-purple-100">
            <h3 class="font-bold text-gray-900 text-sm mb-2">Preview Brand</h3>
            <div class="p-4 bg-white rounded-xl border border-gray-100 text-center">
                <img src="{{ $brand->logoIconUrl() }}" class="h-12 mx-auto mb-2" alt="Logo">
                <p class="font-bold text-emerald-800">{{ old('app_name', $settings['app_name'] ?? $brand->appName()) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ old('app_tagline', $settings['app_tagline'] ?? $brand->tagline()) }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ old('mosque_name', $settings['mosque_name'] ?? $brand->mosqueName()) }}</p>
            </div>
        </div>
        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-2">Rekomendasi</h3>
            <ul class="text-xs text-gray-500 space-y-1.5">
                <li>• Logo PNG transparan, min. 512×512px</li>
                <li>• Logo icon untuk header/navbar</li>
                <li>• Favicon 32×32 atau 64×64px</li>
            </ul>
        </div>
        <div class="card p-5 bg-gray-50 border-gray-200">
            <h3 class="font-semibold text-gray-800 text-sm mb-2">Credit Platform</h3>
            <p class="text-xs text-gray-500 leading-relaxed">
                Footer selalu menampilkan <strong>{{ config('platform.copyright') }}</strong> dan link ke
                <a href="{{ config('platform.developer_url') }}" target="_blank" class="text-emerald-700 underline">{{ config('platform.developer') }}</a>.
                Credit platform tidak dapat diubah.
            </p>
        </div>
    </x-slot:aside>
</x-admin.form-layout>
@endsection
