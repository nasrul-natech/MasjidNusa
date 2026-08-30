@extends('layouts.admin.keuangan')
@php($account = $account ?? new \App\Models\BankAccount)

@section('title', $account->exists ? 'Edit Rekening' : 'Tambah Rekening')
@section('page-title', $account->exists ? 'Edit Rekening Bank' : 'Tambah Rekening Bank')

@section('content')
<x-admin.form-layout>
    <x-slot>
        <form action="{{ $account->exists ? route('admin.keuangan.bank-accounts.update', $account->id) : route('admin.keuangan.bank-accounts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @if($account->exists) @method('PUT') @endif

            <div class="card p-5 lg:p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nama Bank <span class="text-red-500">*</span></label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $account->bank_name ?? '') }}" class="form-input" placeholder="BCA, Mandiri, BSI..." required>
                    </div>
                    <div>
                        <label class="form-label">Nomor Rekening <span class="text-red-500">*</span></label>
                        <input type="text" name="account_number" value="{{ old('account_number', $account->account_number ?? '') }}" class="form-input font-mono" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Nama Pemilik Rekening <span class="text-red-500">*</span></label>
                        <input type="text" name="account_name" value="{{ old('account_name', $account->account_name ?? '') }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Urutan Tampil</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $account->sort_order ?? 0) }}" class="form-input" min="0">
                    </div>
                    <div>
                        <label class="form-label">Logo Bank</label>
                        @if($account->exists && $account->bank_logo)
                        <img src="{{ asset('storage/' . $account->bank_logo) }}" class="h-10 mb-2 object-contain">
                        @endif
                        <input type="file" name="bank_logo" accept="image/*" class="form-input">
                    </div>
                </div>

                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600 w-4 h-4" {{ old('is_active', $account->is_active ?? true) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Aktif (tampil di halaman donasi)</span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary px-6">Simpan</button>
                <a href="{{ route('admin.keuangan.bank-accounts.index') }}" class="btn-secondary px-6">Batal</a>
            </div>
        </form>
    </x-slot>

    <x-slot:aside>
        <div class="card p-5 bg-gradient-to-br from-amber-50 to-orange-50 border-amber-100">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mb-3">
                @include('components.icons.bank', ['class' => 'w-5 h-5 text-amber-700'])
            </div>
            <h3 class="font-bold text-gray-900 text-sm mb-2">Rekening Donasi</h3>
            <p class="text-xs text-gray-600 leading-relaxed">Rekening ini akan ditampilkan di halaman donasi publik untuk transfer manual oleh jamaah.</p>
        </div>
    </x-slot:aside>
</x-admin.form-layout>
@endsection
