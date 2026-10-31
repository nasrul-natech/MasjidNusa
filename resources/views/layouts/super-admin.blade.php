@extends('layouts.admin.base')

@section('admin-skin', 'admin-skin-super')
@section('panel-name', 'Super Admin')

@section('sidebar-menu')
<x-sidebar-link href="{{ route('super-admin.dashboard') }}" :active="request()->routeIs('super-admin.dashboard')" icon="dashboard">Dashboard</x-sidebar-link>

@can('manage_users')
<div class="sidebar-section-title">Manajemen User</div>
<x-sidebar-link href="{{ route('super-admin.users.index') }}" :active="request()->routeIs('super-admin.users.*')" icon="users">Manajemen User</x-sidebar-link>
@endcan
@can('manage_roles')
<x-sidebar-link href="{{ route('super-admin.roles.index') }}" :active="request()->routeIs('super-admin.roles.*')" icon="shield">Role & Permission</x-sidebar-link>
@endcan

<div class="sidebar-section-title">Konfigurasi</div>
@can('manage_smtp')
<x-sidebar-link href="{{ route('super-admin.smtp.index') }}" :active="request()->routeIs('super-admin.smtp.*')" icon="mail">Konfigurasi SMTP</x-sidebar-link>
@endcan
@can('manage_email_templates')
<x-sidebar-link href="{{ route('super-admin.email-templates.index') }}" :active="request()->routeIs('super-admin.email-templates.*')" icon="template">Template Email</x-sidebar-link>
@endcan
@can('manage_payment_rules')
<x-sidebar-link href="{{ route('super-admin.payment-rules.index') }}" :active="request()->routeIs('super-admin.payment-rules.*')" icon="credit-card">Aturan Pembayaran</x-sidebar-link>
@endcan
@can('manage_settings')
<x-sidebar-link href="{{ route('super-admin.settings.index') }}" :active="request()->routeIs('super-admin.settings.*')" icon="cog">Pengaturan Sistem</x-sidebar-link>
@endcan
@can('manage_modules')
<x-sidebar-link href="{{ route('super-admin.modules.index') }}" :active="request()->routeIs('super-admin.modules.*')" icon="puzzle">Manajemen Modul</x-sidebar-link>
@endcan
@can('manage_branding')
<x-sidebar-link href="{{ route('super-admin.branding.index') }}" :active="request()->routeIs('super-admin.branding.*')" icon="paint">Branding Global</x-sidebar-link>
@endcan

@can('view_audit_log')
<div class="sidebar-section-title">Monitoring</div>
<x-sidebar-link href="{{ route('super-admin.audit-log.index') }}" :active="request()->routeIs('super-admin.audit-log.*')" icon="log">Audit Log</x-sidebar-link>
@endcan
@endsection
