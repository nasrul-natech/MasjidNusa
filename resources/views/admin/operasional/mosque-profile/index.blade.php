@extends('layouts.admin.operasional')

@section('title', 'Profil Masjid')
@section('page-title', 'Profil Masjid')

@section('content')
@if(!$mosque)
<div class="card overflow-hidden shadow-md border-2 border-dashed border-emerald-200 p-10 text-center">
    <p class="text-gray-600 mb-4">Data profil masjid belum ada.</p>
    <a href="{{ route('admin.operasional.mosque-profile.edit', 1) }}" class="btn-primary">Buat Profil Masjid</a>
</div>
@else
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.operasional.mosque-profile.edit', $mosque->id) }}" class="btn-primary">Edit Profil</a>
</div>

<x-panel-card title="Profil Masjid" description="Informasi publik masjid untuk website dan layanan jamaah." tone="emerald">
    <x-slot:badge><x-badge type="success">Aktif</x-badge></x-slot:badge>
    @if($mosque->banner)
    <img src="{{ $mosque->banner_url }}" class="w-full h-48 object-cover rounded-xl mb-4 border border-emerald-100" alt="Banner masjid">
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="rounded-xl bg-emerald-50/80 border border-emerald-100 p-3">
            <p class="detail-label">Nama Masjid</p>
            <p class="detail-value">{{ $mosque->name }}</p>
        </div>
        @if($mosque->address)
        <div class="rounded-xl bg-emerald-50/80 border border-emerald-100 p-3">
            <p class="detail-label">Alamat</p>
            <p class="detail-value">{{ $mosque->address }}</p>
        </div>
        @endif
        @if($mosque->phone)
        <div class="rounded-xl bg-white border border-gray-100 p-3">
            <p class="detail-label">Telepon</p>
            <p class="detail-value">{{ $mosque->phone }}</p>
        </div>
        @endif
        @if($mosque->email)
        <div class="rounded-xl bg-white border border-gray-100 p-3">
            <p class="detail-label">Email</p>
            <p class="detail-value">{{ $mosque->email }}</p>
        </div>
        @endif
    </div>

    @if($mosque->description)
    <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-4 mb-3">
        <p class="detail-label">Deskripsi</p>
        <p class="text-sm text-gray-800 leading-relaxed">{{ $mosque->description }}</p>
    </div>
    @endif

    @if($mosque->vision)
    <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-4 mb-3">
        <p class="detail-label text-indigo-900">Visi</p>
        <p class="text-sm text-gray-800 leading-relaxed">{{ $mosque->vision }}</p>
    </div>
    @endif

    @if($mosque->mission)
    <div class="rounded-xl border border-teal-100 bg-teal-50/50 p-4">
        <p class="detail-label text-teal-900">Misi</p>
        <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">{{ $mosque->mission }}</p>
    </div>
    @endif
</x-panel-card>
@endif
@endsection
