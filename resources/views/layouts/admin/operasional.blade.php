@extends('layouts.admin.base')

@section('panel-name', 'Admin Operasional')

@section('sidebar-menu')
@can('access_admin_operasional')
<x-sidebar-link href="{{ route('admin.operasional.dashboard') }}" :active="request()->routeIs('admin.operasional.dashboard')" icon="dashboard">Dashboard</x-sidebar-link>
@endcan
@can('manage_mosque_profile')
<x-sidebar-link href="{{ route('admin.operasional.mosque-profile.index') }}" :active="request()->routeIs('admin.operasional.mosque-profile.*')" icon="building">Profil Masjid</x-sidebar-link>
@endcan
@can('manage_mosque_history')
<x-sidebar-link href="{{ route('admin.operasional.mosque-history.index') }}" :active="request()->routeIs('admin.operasional.mosque-history.*')" icon="clock">Sejarah Masjid</x-sidebar-link>
@endcan
@can('manage_management_history')
<x-sidebar-link href="{{ route('admin.operasional.management-history.index') }}" :active="request()->routeIs('admin.operasional.management-history.*')" icon="users">Sejarah Kepengurusan</x-sidebar-link>
@endcan
@can('manage_organization_structure')
<x-sidebar-link href="{{ route('admin.operasional.organization.index') }}" :active="request()->routeIs('admin.operasional.organization.*')" icon="sitemap">Struktur Organisasi</x-sidebar-link>
@endcan
@can('manage_prayer_schedule')
<x-sidebar-link href="{{ route('admin.operasional.prayer-schedule.index') }}" :active="request()->routeIs('admin.operasional.prayer-schedule.*')" icon="clock">Jadwal Sholat</x-sidebar-link>
@endcan
@can('manage_general_info')
<x-sidebar-link href="{{ route('admin.operasional.doa.index') }}" :active="request()->routeIs('admin.operasional.doa.*') || request()->routeIs('admin.operasional.doa-category.*')" icon="book">Kumpulan Doa</x-sidebar-link>
@endcan
@endsection
