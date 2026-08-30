@extends('layouts.super-admin')

@section('title', 'Konfigurasi Duitku Global')
@section('page-title', 'Konfigurasi Duitku Global')

@section('content')
<x-admin.form-layout>
    <x-slot>
        <form action="{{ route('super-admin.duitku.update') }}" method="POST" class="space-y-4">
            @csrf
            <div class="card p-5 lg:p-6 space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        @include('components.icons.gateway', ['class' => 'w-5 h-5 text-blue-700'])
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Duitku Global</h3>
                        <p class="text-xs text-gray-500">Konfigurasi payment gateway tingkat sistem</p>
                    </div>
                </div>

                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-700">
                    Konfigurasi ini berlaku global. Admin Keuangan juga dapat mengonfigurasi Duitku di panel mereka.
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
                        <label class="form-label">Masa Berlaku (menit)</label>
                        <input type="number" name="expiry_period" value="{{ old('expiry_period', $config->expiry_period ?? 60) }}" class="form-input" min="5" max="1440" required>
                    </div>
                    <div>
                        <label class="form-label">Merchant Code <span class="text-red-500">*</span></label>
                        <input type="text" name="merchant_code" value="{{ old('merchant_code', $config->merchant_code ?? '') }}" class="form-input font-mono" required>
                    </div>
                    <div>
                        <label class="form-label">API Key <span class="text-red-500">*</span></label>
                        <input type="text" name="api_key" value="{{ old('api_key', $config->api_key ?? '') }}" class="form-input font-mono" required>
                    </div>
                </div>

                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600 w-4 h-4" {{ old('is_active', $config->is_active ?? false) ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700">Aktifkan Duitku</span>
                </label>
            </div>
            <button type="submit" class="btn-primary px-6">Simpan</button>
        </form>
    </x-slot>

    <x-slot:aside>
        <div class="card p-5 bg-gradient-to-br from-blue-50 to-indigo-50 border-blue-100">
            @include('components.icons.credit-card', ['class' => 'w-8 h-8 text-blue-600 mb-2'])
            <h3 class="font-bold text-gray-900 text-sm mb-1">Payment Gateway</h3>
            <p class="text-xs text-gray-600">Duitku mendukung VA, QRIS, OVO, GoPay, ShopeePay, dan lainnya.</p>
        </div>
        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-2">Environment</h3>
            <ul class="text-xs text-gray-500 space-y-1.5">
                <li><strong>Sandbox:</strong> Testing tanpa transaksi nyata</li>
                <li><strong>Production:</strong> Transaksi live & real</li>
            </ul>
        </div>
    </x-slot:aside>
</x-admin.form-layout>
@endsection
