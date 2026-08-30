@extends('layouts.super-admin')
@php($user = $user ?? new \App\Models\User)

@section('title', $user->exists ? 'Edit User' : 'Tambah User')
@section('page-title', $user->exists ? 'Edit User' : 'Tambah User Baru')

@section('content')
<x-admin.form-layout>
    <x-slot>
        <form action="{{ $user->exists ? route('super-admin.users.update', $user->id) : route('super-admin.users.store') }}" method="POST" class="space-y-4">
            @csrf
            @if($user->exists) @method('PUT') @endif

            <div class="card p-5 lg:p-6 space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        @include('components.icons.users', ['class' => 'w-5 h-5 text-blue-700'])
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Data Pengguna</h3>
                        <p class="text-xs text-gray-500">{{ $user->exists ? 'Perbarui informasi user' : 'Buat akun admin atau jamaah baru' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Telepon</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Role <span class="text-red-500">*</span></label>
                        <select name="role" class="form-input" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $user->exists ? $user->roles->first()?->name : '') === $role->name ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @if(!$user->exists)
                    <div class="md:col-span-2">
                        <label class="form-label">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" class="form-input" required minlength="8">
                    </div>
                    @endif
                </div>

                @if($user->exists)
                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600 w-4 h-4" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700">Akun aktif</span>
                </label>
                @endif
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary px-6">Simpan</button>
                <a href="{{ route('super-admin.users.index') }}" class="btn-secondary px-6">Batal</a>
            </div>
        </form>
    </x-slot>

    <x-slot:aside>
        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-3">Role Tersedia</h3>
            <div class="space-y-2">
                @foreach($roles as $role)
                <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg text-xs">
                    @include('components.icons.shield', ['class' => 'w-3.5 h-3.5 text-gray-500'])
                    <span class="font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </x-slot:aside>
</x-admin.form-layout>
@endsection
