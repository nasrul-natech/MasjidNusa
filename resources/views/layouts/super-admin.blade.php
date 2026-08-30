@extends('layouts.admin.base')

@section('panel-name', 'Super Admin')

@section('sidebar-menu')
<x-sidebar-link href="{{ route('super-admin.dashboard') }}" :active="request()->routeIs('super-admin.dashboard')" icon="dashboard">Dashboard</x-sidebar-link>

<div class="sidebar-section-title">Manajemen User</div>
<x-sidebar-link href="{{ route('super-admin.users.index') }}" :active="request()->routeIs('super-admin.users.*')" icon="users">Manajemen User</x-sidebar-link>
<x-sidebar-link href="{{ route('super-admin.roles.index') }}" :active="request()->routeIs('super-admin.roles.*')" icon="shield">Role & Permission</x-sidebar-link>

<div class="sidebar-section-title">Konfigurasi</div>
<x-sidebar-link href="{{ route('super-admin.smtp.index') }}" :active="request()->routeIs('super-admin.smtp.*')" icon="mail">Konfigurasi SMTP</x-sidebar-link>
<x-sidebar-link href="{{ route('super-admin.email-templates.index') }}" :active="request()->routeIs('super-admin.email-templates.*')" icon="template">Template Email</x-sidebar-link>
<x-sidebar-link href="{{ route('super-admin.payment-rules.index') }}" :active="request()->routeIs('super-admin.payment-rules.*')" icon="credit-card">Aturan Pembayaran</x-sidebar-link>
<x-sidebar-link href="{{ route('super-admin.settings.index') }}" :active="request()->routeIs('super-admin.settings.*')" icon="cog">Pengaturan Sistem</x-sidebar-link>
<x-sidebar-link href="{{ route('super-admin.modules.index') }}" :active="request()->routeIs('super-admin.modules.*')" icon="puzzle">Manajemen Modul</x-sidebar-link>
<x-sidebar-link href="{{ route('super-admin.branding.index') }}" :active="request()->routeIs('super-admin.branding.*')" icon="paint">Branding Global</x-sidebar-link>

<div class="sidebar-section-title">Monitoring</div>
<x-sidebar-link href="{{ route('super-admin.audit-log.index') }}" :active="request()->routeIs('super-admin.audit-log.*')" icon="log">Audit Log</x-sidebar-link>
@endsection
