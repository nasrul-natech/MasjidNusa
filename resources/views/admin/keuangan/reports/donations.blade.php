@extends('layouts.admin.keuangan')

@section('title', 'Laporan Donasi')
@section('page-title', 'Laporan Donasi')

@section('content')
<div class="flex flex-wrap items-end gap-3 mb-5">
    <form method="GET" class="flex gap-2 items-end">
        <div>
            <label class="form-label text-xs">Periode</label>
            <input type="month" name="month" value="{{ $month }}" class="form-input">
        </div>
        <button type="submit" class="btn-primary px-4">Tampilkan</button>
    </form>
    <a href="{{ route('admin.keuangan.reports.export', ['month' => $month]) }}" class="btn-secondary px-4 ml-auto">⬇ Export CSV</a>
</div>

<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="card p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">Total Terkumpul</p>
        <p class="font-bold text-emerald-700 text-lg">Rp {{ number_format($stats['total'], 0, ',', '.') }}</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">Jumlah Donasi</p>
        <p class="font-bold text-gray-900 text-lg">{{ $stats['count'] }}</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-xs text-gray-500 mb-1">Terverifikasi</p>
        <p class="font-bold text-blue-700 text-lg">{{ $stats['verified'] }}</p>
    </div>
</div>

<x-admin.table title="Laporan Donasi">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Donatur</th>
                <th>Kampanye</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($donations as $donation)
            @php
                $statusColors = ['pending'=>'yellow','waiting_verification'=>'warning','verified'=>'success','paid'=>'success','rejected'=>'danger','expired'=>'gray'];
                $statusLabels = ['pending'=>'Menunggu','waiting_verification'=>'Verifikasi','verified'=>'Terverifikasi','paid'=>'Lunas','rejected'=>'Ditolak','expired'=>'Kadaluwarsa'];
            @endphp
            <tr>
                <td data-label="Kode" class="font-mono text-xs">{{ $donation->donation_code }}</td>
                <td data-label="Donatur">{{ $donation->donor_name }}</td>
                <td data-label="Kampanye" class="text-sm">{{ $donation->campaign?->title ?? 'Umum' }}</td>
                <td data-label="Jumlah" class="font-semibold text-emerald-700">Rp {{ number_format($donation->amount, 0, ',', '.') }}</td>
                <td data-label="Status"><x-badge type="{{ $statusColors[$donation->status] ?? 'gray' }}">{{ $statusLabels[$donation->status] ?? $donation->status }}</x-badge></td>
                <td data-label="Tanggal" class="text-xs text-gray-500">{{ $donation->created_at->format('d/m/Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-8 text-gray-500">Tidak ada data donasi untuk periode ini</td></tr>
            @endforelse
        </tbody>
</x-admin.table>

<div class="mt-4">{{ $donations->withQueryString()->links() }}</div>
@endsection
