@extends('layouts.admin.base')

@section('panel-name', 'Admin Keuangan')

@section('sidebar-menu')
<x-sidebar-link href="{{ route('admin.keuangan.dashboard') }}" :active="request()->routeIs('admin.keuangan.dashboard')" icon="dashboard">Dashboard</x-sidebar-link>

<div class="sidebar-section-title">Program Donasi</div>
<x-sidebar-link href="{{ route('admin.keuangan.campaigns.index') }}" :active="request()->routeIs('admin.keuangan.campaigns.*')" icon="heart">Program Donasi</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.keuangan.donation-categories.index') }}" :active="request()->routeIs('admin.keuangan.donation-categories.*')" icon="archive">Kategori Donasi</x-sidebar-link>

<div class="sidebar-section-title">Keuangan</div>
<x-sidebar-link href="{{ route('admin.keuangan.donations.index') }}" :active="request()->routeIs('admin.keuangan.donations.*')" icon="chart">Data Donasi Masuk</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.keuangan.verify-payment.index') }}" :active="request()->routeIs('admin.keuangan.verify-payment.*')" icon="check">Verifikasi Pembayaran</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.keuangan.bank-accounts.index') }}" :active="request()->routeIs('admin.keuangan.bank-accounts.*')" icon="bank">Rekening Manual</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.keuangan.payment-config.index') }}" :active="request()->routeIs('admin.keuangan.payment-config.*')" icon="cog">Konfigurasi Pembayaran</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.keuangan.duitku.index') }}" :active="request()->routeIs('admin.keuangan.duitku.*')" icon="credit-card">Gateway Duitku</x-sidebar-link>
<x-sidebar-link href="{{ route('admin.keuangan.reports.donations') }}" :active="request()->routeIs('admin.keuangan.reports.*')" icon="report">Laporan Donasi</x-sidebar-link>
@endsection
