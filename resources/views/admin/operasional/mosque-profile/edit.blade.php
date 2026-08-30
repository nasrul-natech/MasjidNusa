@extends('layouts.admin.operasional')

@section('title', 'Edit Profil Masjid')
@section('page-title', 'Edit Profil Masjid')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('admin.operasional.mosque-profile.update', $mosque->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800">Informasi Dasar</h3>

            <div>
                <label class="form-label">Nama Masjid <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $mosque->name) }}" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-input" rows="2">{{ old('address', $mosque->address) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Telepon</label>
                    <input type="tel" name="phone" value="{{ old('phone', $mosque->phone) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $mosque->email) }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-input" rows="3">{{ old('description', $mosque->description) }}</textarea>
            </div>
        </div>

        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800">Visi & Misi</h3>

            <div>
                <label class="form-label">Visi</label>
                <textarea name="vision" class="form-input" rows="2">{{ old('vision', $mosque->vision) }}</textarea>
            </div>

            <div>
                <label class="form-label">Misi</label>
                <textarea name="mission" class="form-input" rows="4" placeholder="1. Poin misi pertama&#10;2. Poin misi kedua">{{ old('mission', $mosque->mission) }}</textarea>
            </div>
        </div>

        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800">Lokasi & Kontak</h3>

            <div>
                <label class="form-label">URL Google Maps</label>
                <input type="url" name="google_maps_url" value="{{ old('google_maps_url', $mosque->google_maps_url) }}" class="form-input" placeholder="https://maps.google.com/...">
            </div>

            <div>
                <label class="form-label">Website</label>
                <input type="url" name="website" value="{{ old('website', $mosque->website) }}" class="form-input" placeholder="https://...">
            </div>
        </div>

        <div class="card p-5 space-y-4">
            <h3 class="font-semibold text-gray-800">Foto & Banner</h3>

            @if($mosque->photo)
            <div>
                <p class="text-xs text-gray-500 mb-1">Foto Saat Ini</p>
                <img src="{{ $mosque->photo_url }}" class="h-24 object-cover rounded-lg">
            </div>
            @endif

            <div>
                <label class="form-label">Foto Masjid</label>
                <input type="file" name="photo" accept="image/*" class="form-input">
            </div>

            @if($mosque->banner)
            <div>
                <p class="text-xs text-gray-500 mb-1">Banner Saat Ini</p>
                <img src="{{ $mosque->banner_url }}" class="h-24 w-full object-cover rounded-lg">
            </div>
            @endif

            <div>
                <label class="form-label">Banner</label>
                <input type="file" name="banner" accept="image/*" class="form-input">
                <p class="text-xs text-gray-400 mt-1">Digunakan sebagai header halaman Info Masjid</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6">Simpan Perubahan</button>
            <a href="{{ route('admin.operasional.mosque-profile.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
