@extends('layouts.admin.operasional')

@section('title', 'Profil Masjid')
@section('page-title', 'Profil Masjid')

@section('content')
@if(!$mosque)
<div class="text-center py-10">
    <p class="text-gray-500 mb-4">Data profil masjid belum ada.</p>
    <a href="{{ route('admin.operasional.mosque-profile.edit', 1) }}" class="btn-primary">Buat Profil Masjid</a>
</div>
@else
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.operasional.mosque-profile.edit', $mosque->id) }}" class="btn-primary">Edit Profil</a>
</div>

<div class="card">
    <div class="card-body space-y-4">
        @if($mosque->banner)
        <img src="{{ $mosque->banner_url }}" class="w-full h-48 object-cover rounded-xl">
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Nama Masjid</p>
                <p class="font-semibold text-gray-900">{{ $mosque->name }}</p>
            </div>
            @if($mosque->address)
            <div>
                <p class="text-xs text-gray-500">Alamat</p>
                <p class="font-semibold text-gray-900">{{ $mosque->address }}</p>
            </div>
            @endif
            @if($mosque->phone)
            <div>
                <p class="text-xs text-gray-500">Telepon</p>
                <p class="font-semibold text-gray-900">{{ $mosque->phone }}</p>
            </div>
            @endif
            @if($mosque->email)
            <div>
                <p class="text-xs text-gray-500">Email</p>
                <p class="font-semibold text-gray-900">{{ $mosque->email }}</p>
            </div>
            @endif
        </div>

        @if($mosque->description)
        <div>
            <p class="text-xs text-gray-500 mb-1">Deskripsi</p>
            <p class="text-sm text-gray-700">{{ $mosque->description }}</p>
        </div>
        @endif

        @if($mosque->vision)
        <div>
            <p class="text-xs text-gray-500 mb-1">Visi</p>
            <p class="text-sm text-gray-700">{{ $mosque->vision }}</p>
        </div>
        @endif

        @if($mosque->mission)
        <div>
            <p class="text-xs text-gray-500 mb-1">Misi</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $mosque->mission }}</p>
        </div>
        @endif
    </div>
</div>
@endif
@endsection
