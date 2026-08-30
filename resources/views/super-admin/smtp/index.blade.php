@extends('layouts.super-admin')

@section('title', 'Konfigurasi SMTP')
@section('page-title', 'Konfigurasi Email SMTP')

@section('content')
<x-admin.form-layout>
    <x-slot>
        @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 mb-4">{{ session('error') }}</div>
        @endif
        @if($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 mb-4">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('super-admin.smtp.update') }}" method="POST" class="space-y-4">
            @csrf
            <div class="card p-5 lg:p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        @include('components.icons.mail', ['class' => 'w-5 h-5 text-blue-700'])
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Konfigurasi SMTP</h3>
                        <p class="text-xs text-gray-500">Pengaturan server email untuk notifikasi sistem</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label">SMTP Host <span class="text-red-500">*</span></label>
                        <input type="text" name="host" value="{{ old('host', $smtp->host ?? 'smtp.gmail.com') }}" class="form-input" required placeholder="smtp.gmail.com">
                    </div>
                    <div>
                        <label class="form-label">Port <span class="text-red-500">*</span></label>
                        <input type="number" name="port" value="{{ old('port', $smtp->port ?? 587) }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Enkripsi</label>
                        <select name="encryption" class="form-input">
                            <option value="tls" {{ ($smtp->encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ ($smtp->encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="none" {{ ($smtp->encryption ?? '') === 'none' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Username / Email</label>
                        <input type="text" name="username" value="{{ old('username', $smtp->username ?? '') }}" class="form-input">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="{{ $smtp && $smtp->password ? '******** (kosongkan jika tidak berubah)' : 'Masukkan password SMTP' }}">
                    </div>
                    <div>
                        <label class="form-label">Nama Pengirim <span class="text-red-500">*</span></label>
                        <input type="text" name="sender_name" value="{{ old('sender_name', $smtp->sender_name ?? 'MasjidNusa') }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Email Pengirim <span class="text-red-500">*</span></label>
                        <input type="email" name="sender_email" value="{{ old('sender_email', $smtp->sender_email ?? '') }}" class="form-input" required>
                    </div>
                </div>

                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 mt-4">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600 w-4 h-4" {{ old('is_active', $smtp->is_active ?? false) ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700">Aktifkan pengiriman email</span>
                </label>
            </div>

            <button type="submit" class="btn-primary px-6">Simpan Konfigurasi</button>
        </form>
    </x-slot>

    <x-slot:aside>
        <div class="card p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-3 h-3 rounded-full {{ $smtp && $smtp->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"></div>
                <span class="text-sm font-semibold text-gray-800">{{ $smtp && $smtp->is_active ? 'SMTP Aktif' : 'SMTP Nonaktif' }}</span>
            </div>
            @if($smtp && $smtp->host)
            <p class="text-xs text-gray-500 font-mono bg-gray-50 p-2 rounded-lg">{{ $smtp->host }}:{{ $smtp->port }}</p>
            @endif
            @if(!$smtp || !$smtp->is_active)
            <p class="text-xs text-amber-600 mt-2">Email tidak terkirim sampai SMTP diaktifkan.</p>
            @endif
        </div>

        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-2 flex items-center gap-2">
                @include('components.icons.mail', ['class' => 'w-4 h-4 text-emerald-600']) Kirim Test Email
            </h3>
            <p class="text-xs text-gray-500 mb-3">Pastikan konfigurasi SMTP benar sebelum digunakan.</p>
            <form action="{{ route('super-admin.smtp.test') }}" method="POST" class="space-y-3">
                @csrf
                <input type="email" name="test_email" placeholder="email@contoh.com" class="form-input text-sm" required>
                <button type="submit" class="btn-primary w-full text-sm">Kirim Test</button>
            </form>
        </div>

        <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-700 space-y-2">
            <p class="font-semibold text-sm">Panduan SMTP</p>
            <ul class="list-disc list-inside text-xs space-y-1 text-blue-600">
                <li><strong>Gmail:</strong> smtp.gmail.com:587 TLS + App Password</li>
                <li><strong>Zoho:</strong> smtp.zoho.com:587 TLS</li>
                <li>Centang aktifkan agar notifikasi email berfungsi</li>
            </ul>
        </div>
    </x-slot:aside>
</x-admin.form-layout>
@endsection
