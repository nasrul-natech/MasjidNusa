@extends('layouts.admin.keuangan')

@section('title', 'Laporan Pembayaran')
@section('page-title', 'Laporan Pembayaran')

@section('content')
<div class="flex items-end gap-3 mb-5">
    <form method="GET" class="flex gap-2 items-end">
        <div>
            <label class="form-label text-xs">Periode</label>
            <input type="month" name="month" value="{{ $month }}" class="form-input">
        </div>
        <button type="submit" class="btn-primary px-4">Tampilkan</button>
    </form>
</div>

<div class="card overflow-hidden">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Kode Transaksi</th>
                <th>Donatur</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Dibayar</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td class="font-mono text-xs">{{ $tx->transaction_code }}</td>
                <td>{{ $tx->donation?->donor_name }}</td>
                <td class="text-sm">{{ $tx->paymentMethod?->name ?? $tx->payment_method }}</td>
                <td><x-badge type="{{ $tx->status === 'paid' ? 'success' : ($tx->status === 'failed' ? 'danger' : 'yellow') }}">{{ ucfirst($tx->status) }}</x-badge></td>
                <td class="font-semibold">Rp {{ number_format($tx->donation?->amount ?? 0, 0, ',', '.') }}</td>
                <td class="text-xs text-gray-500">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-8 text-gray-500">Tidak ada data transaksi untuk periode ini</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $transactions->withQueryString()->links() }}</div>
@endsection
