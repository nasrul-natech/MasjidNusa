@extends('layouts.admin.keuangan')

@section('title', 'Manajemen Donasi')
@section('page-title', 'Manajemen Donasi')

@section('content')
<x-admin.table-filter>
    <div class="filter-field-wide">
        <label class="form-label text-xs">Cari</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode / nama donatur..." class="form-input text-sm">
    </div>
    <div class="filter-field">
        <label class="form-label text-xs">Status</label>
        <select name="status" class="form-input text-sm">
            <option value="">Semua Status</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
            <option value="waiting_verification" {{ request('status') === 'waiting_verification' ? 'selected' : '' }}>Butuh Verifikasi</option>
            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
        </select>
    </div>
</x-admin.table-filter>

<x-admin.table>
    <thead>
        <tr>
            <x-admin.sortable-th column="donation_code" label="Kode" />
            <x-admin.sortable-th column="donor_name" label="Donatur" />
            <th>Kampanye</th>
            <x-admin.sortable-th column="amount" label="Jumlah" />
            <x-admin.sortable-th column="status" label="Status" />
            <x-admin.sortable-th column="created_at" label="Tanggal" />
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($donations as $donation)
        @php
            $statusColors = ['pending'=>'yellow','waiting_verification'=>'warning','verified'=>'success','paid'=>'success','rejected'=>'danger','expired'=>'gray'];
            $statusLabels = ['pending'=>'Menunggu','waiting_verification'=>'Verifikasi','verified'=>'Terverifikasi','paid'=>'Lunas','rejected'=>'Ditolak','expired'=>'Kedaluwarsa'];
        @endphp
        <tr>
            <td class="font-mono text-xs" data-label="Kode">{{ $donation->donation_code }}</td>
            <td data-label="Donatur">
                <p class="font-medium">{{ $donation->donor_name }}</p>
                <p class="text-xs text-gray-400">{{ $donation->donor_email }}</p>
            </td>
            <td class="text-sm" data-label="Kampanye">{{ $donation->campaign?->title ?? 'Umum' }}</td>
            <td class="font-semibold text-emerald-700" data-label="Jumlah">Rp {{ number_format($donation->amount, 0, ',', '.') }}</td>
            <td data-label="Status"><x-badge type="{{ $statusColors[$donation->status] ?? 'gray' }}">{{ $statusLabels[$donation->status] ?? $donation->status }}</x-badge></td>
            <td class="text-xs text-gray-500" data-label="Tanggal">{{ $donation->created_at->format('d/m/Y') }}</td>
            <td data-label="Aksi"><a href="{{ route('admin.keuangan.donations.show', $donation->id) }}" class="btn-action-blue">Detail</a></td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-8 text-gray-500">Belum ada data donasi</td></tr>
        @endforelse
    </tbody>
</x-admin.table>

<div class="mt-4">
    {{ $donations->links() }}
</div>
@endsection
