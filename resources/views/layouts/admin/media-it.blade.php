@extends('layouts.admin.base')

@section('panel-name', 'Admin Media & IT')

@section('sidebar-menu')
<x-sidebar-link href="{{ route('admin.media-it.dashboard') }}" :active="request()->routeIs('admin.media-it.dashboard')" icon="dashboard">Dashboard</x-sidebar-link>

<div class="sidebar-section-title">Kegiatan</div>
<x-sidebar-link href="{{ route('admin.media-it.activities.index', ['filter' => 'upcoming']) }}" :active="request()->is('admin/media-it/activities*') && request()->get('filter') !== 'past'" icon="calendar">Akan Datang</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.media-it.activities.index', ['filter' => 'past']) }}" :active="request()->is('admin/media-it/activities*') && request()->get('filter') === 'past'" icon="archive">Telah Lewat</x-sidebar-link>

<div class="sidebar-section-title">Konten</div>
<x-sidebar-link href="{{ route('admin.media-it.articles.index') }}" :active="request()->routeIs('admin.media-it.articles.*')" icon="document">Artikel / Dakwah</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.media-it.banners.index') }}" :active="request()->routeIs('admin.media-it.banners.*')" icon="photo">Banner & Slider</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.media-it.popup-ads.index') }}" :active="request()->routeIs('admin.media-it.popup-ads.*')" icon="bell">Popup Iklan</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.media-it.notifications.index') }}" :active="request()->routeIs('admin.media-it.notifications.*')" icon="megaphone">Kirim Notifikasi</x-sidebar-link>
@endsection
