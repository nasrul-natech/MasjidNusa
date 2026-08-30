@extends('layouts.admin.media-it')

@section('title', 'Kirim Notifikasi')
@section('page-title', 'Kirim Notifikasi ke Semua Pengguna')

@section('content')
<x-admin.form-layout>
    <x-slot>
        <form action="{{ route('admin.media-it.notifications.send') }}" method="POST" class="space-y-4">
            @csrf
            <div class="card p-5 lg:p-6 space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                        @include('components.icons.megaphone', ['class' => 'w-5 h-5 text-amber-700'])
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">Buat Notifikasi</h3>
                        <p class="text-xs text-gray-500">Kirim ke semua pengguna terdaftar</p>
                    </div>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl text-sm text-amber-700">
                    Notifikasi akan dikirim ke <strong>semua pengguna</strong> yang mengaktifkan push notification.
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="form-label">Judul Notifikasi <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-input" required maxlength="255" placeholder="Contoh: Kegiatan Baru Tersedia">
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Pesan <span class="text-red-500">*</span></label>
                        <textarea name="body" class="form-input" rows="4" required maxlength="500" placeholder="Pesan singkat yang akan ditampilkan di notifikasi...">{{ old('body') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Maks. 500 karakter</p>
                    </div>
                    <div>
                        <label class="form-label">Kategori</label>
                        <select name="category" class="form-input">
                            <option value="general">Umum</option>
                            <option value="activity">Kegiatan</option>
                            <option value="system">Sistem</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Link (URL)</label>
                        <input type="text" name="link" value="{{ old('link') }}" class="form-input" placeholder="/kegiatan atau https://...">
                        <p class="text-xs text-gray-400 mt-1">Halaman saat notifikasi diklik (opsional)</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary px-8">
                @include('components.icons.megaphone', ['class' => 'w-4 h-4 inline -mt-0.5 mr-1']) Kirim Notifikasi
            </button>
        </form>
    </x-slot>

    <x-slot:aside>
        <div class="card p-5 bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-100">
            <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mb-3">
                @include('components.icons.bell', ['class' => 'w-5 h-5 text-emerald-700'])
            </div>
            <h3 class="font-bold text-gray-900 text-sm mb-2">Push Notification</h3>
            <p class="text-xs text-gray-600 leading-relaxed">Notifikasi dikirim ke browser/device pengguna yang sudah mengizinkan notifikasi.</p>
        </div>

        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-3">Cara Kerja</h3>
            <ul class="space-y-3 text-sm text-gray-600">
                <li class="flex items-start gap-2">
                    <span class="w-5 h-5 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center shrink-0 text-[10px] font-bold">1</span>
                    <span>Notifikasi in-app tersimpan di database semua user</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="w-5 h-5 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center shrink-0 text-[10px] font-bold">2</span>
                    <span>Push notification ke device yang mengizinkan</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="w-5 h-5 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center shrink-0 text-[10px] font-bold">3</span>
                    <span>Bekerja di Android Chrome, Firefox, iOS Safari 16.4+</span>
                </li>
            </ul>
        </div>

        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 text-sm mb-2">Tips Penulisan</h3>
            <ul class="text-xs text-gray-500 space-y-1.5">
                <li>• Judul singkat & jelas (max 50 karakter ideal)</li>
                <li>• Pesan to-the-point, sertakan call-to-action</li>
                <li>• Gunakan link ke halaman relevan</li>
            </ul>
        </div>
    </x-slot:aside>
</x-admin.form-layout>
@endsection
