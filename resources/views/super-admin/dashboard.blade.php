@extends('layouts.super-admin')

@section('title', 'Super Admin Dashboard')
@section('page-title', 'Dashboard Super Admin')
@section('page-subtitle', 'Ringkasan sistem & pengguna')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card variant="filled" label="Total User" :value="number_format($stats['total_users'])" iconName="users" color="blue" :href="route('super-admin.users.index')" />
    <x-stat-card variant="filled" label="User Aktif" :value="number_format($stats['active_users'])" iconName="check" color="emerald" />
    <x-stat-card variant="filled" label="Total Donasi" :value="'Rp '.number_format($stats['total_donations'], 0, ',', '.')" iconName="donation" color="amber" />
    <x-stat-card variant="filled" label="Login Hari Ini" :value="$stats['today_logins']" iconName="log" color="purple" />
</div>

<div class="card overflow-hidden">
    <div class="card-header bg-gradient-to-r from-gray-800 to-gray-900 border-0 flex justify-between items-center">
        <h3 class="font-semibold text-white flex items-center gap-2">
            @include('components.icons.log', ['class' => 'w-4 h-4'])
            Aktivitas Terbaru (Audit Log)
        </h3>
        <a href="{{ route('super-admin.audit-log.index') }}" class="text-xs text-white/80 hover:text-white">Lihat semua →</a>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($recentLogs as $log)
        <div class="p-4 flex justify-between items-start hover:bg-gray-50 transition-colors">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                    @include('components.icons.shield', ['class' => 'w-4 h-4 text-gray-600'])
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</p>
                    <p class="text-xs text-gray-500">{{ $log->action }}</p>
                </div>
            </div>
            <p class="text-xs text-gray-400 shrink-0 ml-3">{{ $log->created_at->diffForHumans() }}</p>
        </div>
        @empty
        <p class="p-6 text-sm text-gray-500 text-center">Belum ada aktivitas</p>
        @endforelse
    </div>
</div>
@endsection
