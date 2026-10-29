@extends('layouts.admin.keuangan')

@section('title', 'Dashboard Keuangan')
@section('page-title', 'Dashboard Keuangan')
@section('page-subtitle', 'Ringkasan donasi & keuangan masjid')

@section('content')
{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card variant="filled" label="Total Donasi" :value="'Rp '.number_format($stats['total_donations'], 0, ',', '.')" iconName="donation" color="emerald" :href="route('admin.keuangan.donations.index')" />
    <x-stat-card variant="filled" label="Jumlah Transaksi" :value="number_format($stats['total_count'])" iconName="chart" color="blue" :href="route('admin.keuangan.donations.index')" />
    <x-stat-card variant="filled" label="Menunggu Verifikasi" :value="$stats['pending_verification']" iconName="check" color="amber" :href="route('admin.keuangan.verify-payment.index')" />
    <x-stat-card variant="filled" label="Bulan Ini" :value="'Rp '.number_format($stats['this_month'], 0, ',', '.')" iconName="calendar" color="purple" />
</div>

{{-- Charts Row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-gray-900">Tren Donasi 6 Bulan</h3>
                <p class="text-xs text-gray-500 mt-0.5">Total donasi terverifikasi per bulan</p>
            </div>
            @include('components.icons.chart', ['class' => 'w-5 h-5 text-emerald-600'])
        </div>
        <div class="h-56">
            <canvas id="donationChart"></canvas>
        </div>
    </div>

    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-gray-900">Status Donasi</h3>
                <p class="text-xs text-gray-500 mt-0.5">Distribusi status transaksi</p>
            </div>
            @include('components.icons.report', ['class' => 'w-5 h-5 text-blue-600'])
        </div>
        <div class="h-48 flex items-center justify-center">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

{{-- Recent Donations Table --}}
<x-admin.table>
    <thead>
        <tr>
            <th>Donatur</th>
            <th>Kampanye</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Waktu</th>
        </tr>
    </thead>
    <tbody>
        @forelse($recentDonations as $donation)
        <tr>
            <td data-label="Donatur">
                <p class="font-medium text-gray-900">{{ $donation->donor_name }}</p>
                <p class="text-xs text-gray-500">{{ $donation->donor_email }}</p>
            </td>
            <td class="text-sm" data-label="Kampanye">{{ $donation->campaign?->title ?? 'Donasi Umum' }}</td>
            <td class="font-semibold text-emerald-700" data-label="Jumlah">Rp {{ number_format($donation->amount, 0, ',', '.') }}</td>
            <td data-label="Status">
                @php
                    $statusColors = ['pending'=>'yellow','waiting_verification'=>'warning','verified'=>'success','paid'=>'success','rejected'=>'danger','expired'=>'gray'];
                    $statusLabels = ['pending'=>'Menunggu','waiting_verification'=>'Verifikasi','verified'=>'Terverifikasi','paid'=>'Lunas','rejected'=>'Ditolak','expired'=>'Kedaluwarsa'];
                @endphp
                <x-badge type="{{ $statusColors[$donation->status] ?? 'gray' }}">{{ $statusLabels[$donation->status] ?? $donation->status }}</x-badge>
            </td>
            <td class="text-xs text-gray-500" data-label="Waktu">{{ $donation->created_at->diffForHumans() }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada donasi</td></tr>
        @endforelse
    </tbody>
</x-admin.table>

<div class="mt-3 text-right">
    <a href="{{ route('admin.keuangan.donations.index') }}" class="text-sm text-emerald-600 font-medium hover:underline">Lihat semua donasi →</a>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const months = @json($months);
    const statusData = @json($statusBreakdown);
    const brandColor = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#064e3b';
    const accentColor = getComputedStyle(document.documentElement).getPropertyValue('--color-accent').trim() || '#c59d3f';

    new Chart(document.getElementById('donationChart'), {
        type: 'bar',
        data: {
            labels: months.map(m => m.label),
            datasets: [{
                label: 'Total Donasi (Rp)',
                data: months.map(m => m.total),
                backgroundColor: brandColor,
                borderColor: brandColor,
                borderWidth: 1,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') }
                }
            }
        }
    });

    const statusLabels = {
        pending: 'Menunggu', waiting_verification: 'Verifikasi', verified: 'Terverifikasi',
        paid: 'Lunas', rejected: 'Ditolak', expired: 'Kedaluwarsa'
    };
    const statusColors = [brandColor,'#3b82f6',accentColor,'#8b5cf6','#ef4444','#6b7280',accentColor];

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(k => statusLabels[k] || k),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: statusColors.slice(0, Object.keys(statusData).length),
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
        }
    });
});
</script>
@endpush
