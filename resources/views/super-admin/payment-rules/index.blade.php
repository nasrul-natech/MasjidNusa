@extends('layouts.super-admin')

@section('title', 'Aturan Pembayaran')
@section('page-title', 'Aturan & Konfigurasi Pembayaran')

@section('content')
<x-admin.form-layout>
    <x-slot>
        <form action="{{ route('super-admin.payment-rules.update') }}" method="POST" class="space-y-4">
            @csrf
            <div class="card p-5 lg:p-6 space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                        @include('components.icons.credit-card', ['class' => 'w-5 h-5 text-emerald-700'])
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Aturan Donasi</h3>
                        <p class="text-xs text-gray-500">Konfigurasi global metode & batas donasi</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nominal Minimum Donasi</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm font-medium">Rp</span>
                            <input type="number" name="min_donation_amount" value="{{ old('min_donation_amount', $configs['min_donation_amount'] ?? 1000) }}" class="form-input" min="0" step="1000">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Batas Upload Bukti (menit)</label>
                        <input type="number" name="proof_upload_deadline" value="{{ old('proof_upload_deadline', $configs['proof_upload_deadline'] ?? 60) }}" class="form-input" min="10">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Metode Pembayaran Aktif</label>
                        <select name="active_payment_method" class="form-input">
                            <option value="manual" {{ ($configs['active_payment_method'] ?? 'both') === 'manual' ? 'selected' : '' }}>Manual Transfer Saja</option>
                            <option value="duitku" {{ ($configs['active_payment_method'] ?? '') === 'duitku' ? 'selected' : '' }}>Duitku Saja</option>
                            <option value="both" {{ ($configs['active_payment_method'] ?? '') === 'both' ? 'selected' : '' }}>Keduanya</option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-primary px-6">Simpan Aturan</button>
        </form>
    </x-slot>

    <x-slot:aside>
        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-3">Metode Pembayaran</h3>
            <div class="space-y-2 text-xs text-gray-600">
                <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                    @include('components.icons.bank', ['class' => 'w-4 h-4 text-amber-600'])
                    <span><strong>Manual:</strong> Transfer + upload bukti</span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                    @include('components.icons.credit-card', ['class' => 'w-4 h-4 text-blue-600'])
                    <span><strong>Duitku:</strong> VA, QRIS, e-wallet</span>
                </div>
            </div>
        </div>
        <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl text-xs text-amber-700">
            Aturan ini berlaku global untuk seluruh modul donasi di platform.
        </div>
    </x-slot:aside>
</x-admin.form-layout>
@endsection
