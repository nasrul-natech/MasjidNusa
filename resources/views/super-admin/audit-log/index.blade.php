@extends('layouts.super-admin')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log Aktivitas')

@section('content')

{{-- Stats + Purge Controls --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
    {{-- Stats --}}
    <div class="card p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Statistik Log
        </h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Total log tersimpan</span>
                <span class="font-semibold text-gray-800">{{ number_format($stats['total']) }} entri</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Log tertua</span>
                <span class="font-medium text-gray-600">{{ $stats['oldest'] ? \Carbon\Carbon::parse($stats['oldest'])->format('d M Y') : '-' }}</span>
            </div>
            <hr class="border-gray-100">
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Log &gt; 1 bulan</span>
                <span class="font-medium {{ $stats['month_1'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">{{ number_format($stats['month_1']) }} entri</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Log &gt; 6 bulan</span>
                <span class="font-medium {{ $stats['month_6'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ number_format($stats['month_6']) }} entri</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Log &gt; 1 tahun</span>
                <span class="font-medium {{ $stats['year_1'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ number_format($stats['year_1']) }} entri</span>
            </div>
        </div>
    </div>

    {{-- Purge Controls --}}
    <div class="card p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-1 flex items-center gap-2">
            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Bersihkan Log Lama
        </h3>
        <p class="text-xs text-gray-400 mb-4">Hapus log berdasarkan rentang waktu untuk menjaga performa database.</p>
        <div class="space-y-2">
            <form action="{{ route('super-admin.audit-log.purge') }}" method="POST"
                  onsubmit="return confirm('Hapus semua log yang lebih dari 1 bulan? ({{ number_format($stats['month_1']) }} entri)')">
                @csrf
                <input type="hidden" name="period" value="1_month">
                <button type="submit" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm border border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors {{ $stats['month_1'] == 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $stats['month_1'] == 0 ? 'disabled' : '' }}>
                    <span class="font-medium">Hapus log &gt; 1 bulan</span>
                    <span class="text-xs bg-amber-200 text-amber-800 px-2 py-0.5 rounded-full">{{ number_format($stats['month_1']) }} entri</span>
                </button>
            </form>
            <form action="{{ route('super-admin.audit-log.purge') }}" method="POST"
                  onsubmit="return confirm('Hapus semua log yang lebih dari 6 bulan? ({{ number_format($stats['month_6']) }} entri)')">
                @csrf
                <input type="hidden" name="period" value="6_months">
                <button type="submit" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm border border-orange-200 text-orange-700 bg-orange-50 hover:bg-orange-100 transition-colors {{ $stats['month_6'] == 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $stats['month_6'] == 0 ? 'disabled' : '' }}>
                    <span class="font-medium">Hapus log &gt; 6 bulan</span>
                    <span class="text-xs bg-orange-200 text-orange-800 px-2 py-0.5 rounded-full">{{ number_format($stats['month_6']) }} entri</span>
                </button>
            </form>
            <form action="{{ route('super-admin.audit-log.purge') }}" method="POST"
                  onsubmit="return confirm('Hapus semua log yang lebih dari 1 tahun? ({{ number_format($stats['year_1']) }} entri)')">
                @csrf
                <input type="hidden" name="period" value="1_year">
                <button type="submit" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm border border-red-200 text-red-700 bg-red-50 hover:bg-red-100 transition-colors {{ $stats['year_1'] == 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $stats['year_1'] == 0 ? 'disabled' : '' }}>
                    <span class="font-medium">Hapus log &gt; 1 tahun</span>
                    <span class="text-xs bg-red-200 text-red-800 px-2 py-0.5 rounded-full">{{ number_format($stats['year_1']) }} entri</span>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700 flex items-center gap-2">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ session('error') }}</div>
@endif

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-4">
    <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter action..." class="form-input w-48 text-sm">
    <input type="date" name="date" value="{{ request('date') }}" class="form-input w-auto text-sm">
    <button type="submit" class="btn-primary px-4 text-sm">Filter</button>
    @if(request()->hasAny(['action', 'date', 'user_id']))
    <a href="{{ route('super-admin.audit-log.index') }}" class="btn-secondary px-4 text-sm">Reset</a>
    @endif
</form>

<div class="card overflow-hidden">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>User</th>
                <th>Action</th>
                <th>Data</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td class="text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                <td>
                    @if($log->user)
                    <p class="text-sm font-medium">{{ $log->user->name }}</p>
                    <p class="text-xs text-gray-400">{{ $log->user->email }}</p>
                    @else
                    <span class="text-xs text-gray-400">System</span>
                    @endif
                </td>
                <td>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-blue-50 text-blue-700">{{ $log->action }}</span>
                </td>
                <td class="text-xs text-gray-500 max-w-xs">
                    @if($log->properties)
                    <span class="font-mono text-xs break-all line-clamp-2">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</span>
                    @else
                    -
                    @endif
                </td>
                <td class="text-xs text-gray-400 font-mono">{{ $log->ip_address ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada log aktivitas</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $logs->withQueryString()->links() }}</div>
@endsection
