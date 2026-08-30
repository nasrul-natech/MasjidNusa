@extends('layouts.admin.keuangan')

@section('title', 'Konfigurasi Duitku')
@section('page-title', 'Konfigurasi Duitku Payment Gateway')

@section('content')
<x-admin.form-layout>
    <x-slot>
        <form action="{{ route('admin.keuangan.duitku.update') }}" method="POST" class="space-y-4">
            @csrf
            <div class="card p-5 lg:p-6 space-y-5">
                <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-700">
                    Dapatkan Merchant Code dan API Key di
                    <a href="https://sandbox.duitku.com" target="_blank" class="underline font-semibold">sandbox.duitku.com</a> (testing)
                    atau <a href="https://passport.duitku.com" target="_blank" class="underline font-semibold">passport.duitku.com</a> (production).
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Environment</label>
                        <select name="environment" class="form-input">
                            <option value="sandbox" {{ ($config->environment ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                            <option value="production" {{ ($config->environment ?? '') === 'production' ? 'selected' : '' }}>Production</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Masa Berlaku Transaksi (menit)</label>
                        <input type="number" name="expiry_period" value="{{ old('expiry_period', $config->expiry_period ?? 60) }}" class="form-input" min="5" max="1440" required>
                        <p class="text-xs text-gray-400 mt-1">5–1440 menit (maks. 24 jam)</p>
                    </div>
                    <div>
                        <label class="form-label">Merchant Code <span class="text-red-500">*</span></label>
                        <input type="text" name="merchant_code" value="{{ old('merchant_code', $config->merchant_code ?? '') }}" class="form-input font-mono" required>
                    </div>
                    <div>
                        <label class="form-label">API Key <span class="text-red-500">*</span></label>
                        <input type="text" name="api_key" value="{{ old('api_key', $config->api_key ?? '') }}" class="form-input font-mono" required>
                    </div>
                    <div>
                        <label class="form-label">Callback URL</label>
                        <input type="text" value="{{ config('app.url') }}/webhook/duitku/callback" class="form-input bg-gray-50 text-gray-500" readonly>
                    </div>
                    <div>
                        <label class="form-label">Return URL</label>
                        <input type="text" value="{{ config('app.url') }}/webhook/duitku/return" class="form-input bg-gray-50 text-gray-500" readonly>
                    </div>
                </div>

                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600 w-4 h-4" {{ old('is_active', $config->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="text-sm font-semibold text-gray-800">Aktifkan Duitku</span>
                        <p class="text-xs text-gray-500 mt-0.5">Donatur dapat bayar via Virtual Account, QRIS, e-wallet</p>
                    </div>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary px-6">Simpan Konfigurasi</button>
                <a href="{{ route('admin.keuangan.payment-config.index') }}" class="btn-secondary px-6">Batal</a>
            </div>
        </form>
    </x-slot>

    <x-slot:aside>
        <div class="card p-5 bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-100">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mb-3">
                @include('components.icons.credit-card', ['class' => 'w-5 h-5 text-emerald-700'])
            </div>
            <h3 class="font-bold text-gray-900 text-sm mb-2">Tentang Duitku</h3>
            <p class="text-xs text-gray-600 leading-relaxed">Payment gateway resmi untuk menerima donasi online via VA, QRIS, OVO, GoPay, dan lainnya.</p>
        </div>
        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-3">Langkah Setup</h3>
            <ol class="space-y-2 text-xs text-gray-600">
                <li class="flex gap-2"><span class="w-5 h-5 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center shrink-0 font-bold text-[10px]">1</span>Daftar akun Duitku</li>
                <li class="flex gap-2"><span class="w-5 h-5 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center shrink-0 font-bold text-[10px]">2</span>Salin Merchant Code & API Key</li>
                <li class="flex gap-2"><span class="w-5 h-5 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center shrink-0 font-bold text-[10px]">3</span>Test di Sandbox dulu</li>
                <li class="flex gap-2"><span class="w-5 h-5 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center shrink-0 font-bold text-[10px]">4</span>Aktifkan Production</li>
            </ol>
        </div>
        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-2">Tautan Terkait</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.keuangan.payment-config.index') }}" class="flex items-center gap-2 text-xs text-emerald-700 hover:underline">
                    @include('components.icons.cog', ['class' => 'w-3.5 h-3.5']) Konfigurasi Pembayaran
                </a>
                <a href="{{ route('admin.keuangan.bank-accounts.index') }}" class="flex items-center gap-2 text-xs text-emerald-700 hover:underline">
                    @include('components.icons.bank', ['class' => 'w-3.5 h-3.5']) Rekening Manual
                </a>
            </div>
        </div>
    </x-slot:aside>
</x-admin.form-layout>
@endsection
