@extends('layouts.admin.operasional')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Operasional')
@section('page-subtitle', 'Ringkasan operasional masjid')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card variant="filled" label="Jadwal Aktif" :value="$prayerCount" iconName="clock" color="emerald" :href="route('admin.operasional.prayer-schedule.index')" />
    <x-stat-card variant="filled" label="Kegiatan Publish" :value="$activityCount" iconName="calendar" color="blue" />
    <x-stat-card variant="filled" label="Kegiatan Mendatang" :value="$upcomingCount" iconName="megaphone" color="amber" />
    <x-stat-card variant="filled" label="Profil Masjid" :value="$mosque ? 'Lengkap' : 'Belum'" iconName="building" color="purple" :href="route('admin.operasional.mosque-profile.index')" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Quick Actions --}}
    <div class="card overflow-hidden">
        <div class="card-header bg-gradient-to-r from-emerald-700 to-emerald-600 border-0">
            <h3 class="font-semibold text-white">Aksi Cepat</h3>
        </div>
        <div class="card-body space-y-1 p-2">
            <a href="{{ route('admin.operasional.mosque-profile.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-emerald-50 transition-colors group">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                    @include('components.icons.building', ['class' => 'w-5 h-5 text-emerald-700'])
                </div>
                <span class="text-sm font-medium text-gray-700 flex-1">Edit Profil Masjid</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('admin.operasional.prayer-schedule.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition-colors group">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    @include('components.icons.clock', ['class' => 'w-5 h-5 text-blue-700'])
                </div>
                <span class="text-sm font-medium text-gray-700 flex-1">Tambah Jadwal Sholat</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('admin.operasional.doa.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-teal-50 transition-colors group">
                <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center group-hover:bg-teal-200 transition-colors">
                    @include('components.icons.book', ['class' => 'w-5 h-5 text-teal-700'])
                </div>
                <span class="text-sm font-medium text-gray-700 flex-1">Tambah Doa Baru</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('admin.operasional.organization.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-purple-50 transition-colors group">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    @include('components.icons.sitemap', ['class' => 'w-5 h-5 text-purple-700'])
                </div>
                <span class="text-sm font-medium text-gray-700 flex-1">Struktur Organisasi</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

    {{-- Mosque Info --}}
    @if($mosque)
    <div class="card overflow-hidden">
        <div class="card-header bg-gradient-to-r from-purple-600 to-indigo-600 border-0 flex justify-between items-center">
            <h3 class="font-semibold text-white">Info Masjid</h3>
            <a href="{{ route('admin.operasional.mosque-profile.index') }}" class="text-xs text-white/80 hover:text-white">Edit →</a>
        </div>
        <div class="card-body space-y-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
                    @include('components.icons.building', ['class' => 'w-5 h-5 text-purple-700'])
                </div>
                <div>
                    <p class="font-bold text-gray-900">{{ $mosque->name }}</p>
                    @if($mosque->address) <p class="text-sm text-gray-500 mt-1">{{ $mosque->address }}</p> @endif
                </div>
            </div>
            @if($mosque->phone)
            <div class="flex items-center gap-2 text-sm text-gray-600 p-3 bg-gray-50 rounded-xl">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                {{ $mosque->phone }}
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="card p-8 text-center border-2 border-dashed border-gray-200">
        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            @include('components.icons.building', ['class' => 'w-7 h-7 text-gray-400'])
        </div>
        <p class="font-semibold text-gray-700">Profil masjid belum diisi</p>
        <a href="{{ route('admin.operasional.mosque-profile.index') }}" class="btn-primary text-sm mt-4 inline-flex">Lengkapi Profil</a>
    </div>
    @endif
</div>
@endsection
