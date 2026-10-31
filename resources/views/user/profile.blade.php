@extends('layouts.public')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6 user-page-shell">
    <div class="max-w-lg mx-auto">
        <h1 class="text-xl font-bold text-gray-900 mb-6">Profil Saya</h1>
        @if(\App\Support\NewFeature::enabled('activity_attendance') && $user->isJamaah() && !$user->isAdmin())
        <div class="card overflow-hidden mb-5 shadow-md">
            <div class="card-header bg-gradient-to-r from-emerald-600 to-teal-600 border-0 py-3 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-bold text-white">Kehadiran &amp; Poin</h2>
                <x-badge type="success" class="!bg-white/20 !text-white ring-1 ring-white/30">Jamaah</x-badge>
            </div>
            <div class="card-body flex flex-col sm:flex-row gap-3 bg-gradient-to-b from-emerald-50/50 to-white p-4">
                <a href="{{ route('user.attendance') }}" class="btn-secondary text-center flex-1 shadow-sm border-emerald-200 hover:bg-emerald-50">Kehadiran Saya</a>
                <a href="{{ route('user.attendance.leaderboard') }}" class="btn-primary text-center flex-1 shadow-md">Papan Peringkat</a>
            </div>
        </div>
        @endif

        {{-- Avatar --}}
        <div class="flex items-center gap-4 mb-6 p-5 card">
            <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-16 h-16 rounded-full object-cover border-2 border-emerald-200">
            <div>
                <h2 class="font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <x-badge type="success" class="mt-1">{{ $user->getRoleNames()->first() }}</x-badge>
            </div>
        </div>

        <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="card p-5 space-y-4">
                <h3 class="font-semibold text-gray-800">Informasi Pribadi</h3>

                <div>
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" value="{{ $user->email }}" class="form-input bg-gray-50" disabled>
                </div>

                <div>
                    <label class="form-label">No. HP</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" placeholder="08xxxxxxxxxx">
                </div>

                <div>
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="gender" class="form-input">
                        <option value="">-- Pilih --</option>
                        <option value="L" {{ $user->gender === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ $user->gender === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}" class="form-input">
                </div>

                <div>
                    <label class="form-label">Alamat</label>
                    <textarea name="address" class="form-input" rows="2" placeholder="Alamat lengkap">{{ old('address', $user->address) }}</textarea>
                </div>

                <div>
                    <label class="form-label">Foto Profil</label>
                    <input type="file" name="avatar" accept="image/*" class="form-input">
                </div>
            </div>

            <div class="card p-5 space-y-4">
                <h3 class="font-semibold text-gray-800">Ganti Password</h3>
                <p class="text-xs text-gray-500">Kosongkan jika tidak ingin mengubah password. Untuk mengganti password, isi password saat ini.</p>

                <div>
                    <label class="form-label">Password Saat Ini</label>
                    <input type="password" name="current_password" class="form-input @error('current_password') border-red-500 @enderror">
                    @error('current_password') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-input" placeholder="Minimal 8 karakter">
                </div>

                <div>
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-input">
                </div>
            </div>

            @if(\App\Support\GoogleAuth::isConfigured() && $user->isJamaah())
            <div class="card p-5 space-y-3">
                <h3 class="font-semibold text-gray-800">Akun Google</h3>
                @if($user->google_id)
                <p class="text-sm text-gray-600">Akun ini sudah ditautkan ke Google.</p>
                @else
                <p class="text-sm text-gray-600">Tautkan Google agar bisa masuk dengan akun yang sama. Penautan tidak terjadi otomatis.</p>
                <a href="{{ route('auth.google', ['intent' => 'link']) }}" class="btn-primary inline-flex px-4 py-2.5">Tautkan Google</a>
                @endif
            </div>
            @endif

            <button type="submit" class="btn-primary w-full py-3">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection
