@extends('layouts.admin.keuangan')

@section('title', 'Konfigurasi Pembayaran')
@section('page-title', 'Konfigurasi Pembayaran')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="card p-5 lg:p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                @include('components.icons.cog', ['class' => 'w-5 h-5 text-emerald-700'])
            </div>
            <div>
                <h3 class="font-bold text-gray-900">Metode Pembayaran Aktif</h3>
                <p class="text-xs text-gray-500">Pilih cara donatur melakukan pembayaran</p>
            </div>
        </div>
        <form action="{{ route('admin.keuangan.payment-config.update') }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-3">
                <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors {{ $activeMethod === 'manual' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="active_payment_method" value="manual" class="mt-0.5" {{ $activeMethod === 'manual' ? 'checked' : '' }}>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            @include('components.icons.bank', ['class' => 'w-4 h-4 text-emerald-600'])
                            <p class="font-semibold text-gray-900">Transfer Manual</p>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Donatur transfer dan upload bukti pembayaran untuk diverifikasi admin</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors {{ $activeMethod === 'duitku' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="active_payment_method" value="duitku" class="mt-0.5" {{ $activeMethod === 'duitku' ? 'checked' : '' }}>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            @include('components.icons.credit-card', ['class' => 'w-4 h-4 text-emerald-600'])
                            <p class="font-semibold text-gray-900">Duitku Payment Gateway</p>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Pembayaran otomatis via VA, QRIS, e-wallet</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-colors {{ $activeMethod === 'both' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" name="active_payment_method" value="both" class="mt-0.5" {{ $activeMethod === 'both' ? 'checked' : '' }}>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            @include('components.icons.gateway', ['class' => 'w-4 h-4 text-emerald-600'])
                            <p class="font-semibold text-gray-900">Keduanya</p>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Tampilkan kedua opsi, donatur memilih sendiri</p>
                    </div>
                </label>
            </div>
            <button type="submit" class="btn-primary px-6">Simpan Konfigurasi</button>
        </form>
    </div>

    <div class="space-y-4">
        <a href="{{ route('admin.keuangan.duitku.index') }}" class="card p-5 flex items-center gap-4 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                @include('components.icons.credit-card', ['class' => 'w-6 h-6 text-blue-700'])
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900">Konfigurasi Duitku</h3>
                <p class="text-sm text-gray-500 mt-0.5">Atur Merchant Code & API Key</p>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('admin.keuangan.bank-accounts.index') }}" class="card p-5 flex items-center gap-4 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                @include('components.icons.bank', ['class' => 'w-6 h-6 text-amber-700'])
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900">Rekening Bank Transfer</h3>
                <p class="text-sm text-gray-500 mt-0.5">Kelola rekening tujuan transfer manual</p>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('admin.keuangan.verify-payment.index') }}" class="card p-5 flex items-center gap-4 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                @include('components.icons.check', ['class' => 'w-6 h-6 text-emerald-700'])
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900">Verifikasi Pembayaran</h3>
                <p class="text-sm text-gray-500 mt-0.5">Tinjau bukti transfer dari donatur</p>
            </div>
            <svg class="w-5 h-5 text-gray-400 group-hover:text-emerald-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
</div>
@endsection
