@extends('layouts.admin.base')

@section('admin-skin', 'admin-skin-media-it')
@section('panel-name', 'Admin Media & IT')

@section('sidebar-menu')
@can('access_admin_media_it')
<x-sidebar-link href="{{ route('admin.media-it.dashboard') }}" :active="request()->routeIs('admin.media-it.dashboard')" icon="dashboard">Dashboard</x-sidebar-link>
@endcan

@can('manage_activities')
<div class="sidebar-section-title">Kegiatan</div>
<x-sidebar-link href="{{ route('admin.media-it.activities.index', ['filter' => 'upcoming']) }}" :active="request()->is('admin/media-it/activities*') && request()->get('filter') !== 'past'" icon="calendar">Akan Datang</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.media-it.activities.index', ['filter' => 'past']) }}" :active="request()->is('admin/media-it/activities*') && request()->get('filter') === 'past'" icon="archive">Telah Lewat</x-sidebar-link>
@endcan

@canany(['manage_articles', 'manage_banners', 'manage_popup_ads', 'manage_home_content'])
<div class="sidebar-section-title">Konten</div>
@endcanany
@can('manage_articles')
<x-sidebar-link href="{{ route('admin.media-it.articles.index') }}" :active="request()->routeIs('admin.media-it.articles.*')" icon="document">Artikel / Dakwah</x-sidebar-link>
@endcan
@can('manage_banners')
<x-sidebar-link href="{{ route('admin.media-it.banners.index') }}" :active="request()->routeIs('admin.media-it.banners.*')" icon="photo">Banner & Slider</x-sidebar-link>
@endcan
@can('manage_popup_ads')
<x-sidebar-link href="{{ route('admin.media-it.popup-ads.index') }}" :active="request()->routeIs('admin.media-it.popup-ads.*')" icon="bell">Popup Iklan</x-sidebar-link>
@endcan
@can('manage_home_content')
<x-sidebar-link href="{{ route('admin.media-it.notifications.index') }}" :active="request()->routeIs('admin.media-it.notifications.*')" icon="megaphone">Kirim Notifikasi</x-sidebar-link>
@endcan
@if(\App\Support\NewFeature::enabled('mosque_display'))
@can('manage_mosque_display')
<div class="sidebar-section-title">Display Masjid</div>
<x-sidebar-link href="{{ route('admin.media-it.mosque-display.index') }}" :active="request()->routeIs('admin.media-it.mosque-display.*')" icon="photo">Pengaturan TV & Playlist</x-sidebar-link>
@endcan
@endif
@endsection
