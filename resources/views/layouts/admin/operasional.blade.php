@extends('layouts.admin.base')

@section('admin-skin', 'admin-skin-operasional')
@section('panel-name', 'Admin Operasional')

@section('sidebar-menu')
@if(\App\Support\NewFeature::enabled('activity_attendance'))
@can('manage_activity_attendance')
<x-sidebar-link href="{{ route('admin.operasional.attendance.index') }}" :active="request()->routeIs('admin.operasional.attendance.*')" icon="users">Absensi Kegiatan</x-sidebar-link>
@endcan
@endif
@if(\App\Support\NewFeature::enabled('attendance_rewards'))
@can('view_attendance_rewards')
<x-sidebar-link href="{{ route('admin.operasional.attendance-rewards.index') }}" :active="request()->routeIs('admin.operasional.attendance-rewards.*')" icon="users">Poin Kehadiran Jamaah</x-sidebar-link>
@endcan
@endif
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
