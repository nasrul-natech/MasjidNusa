@extends('layouts.admin.keuangan')

@section('title', 'Rekening Bank')
@section('page-title', 'Rekening Bank')

@section('content')
<div class="flex justify-end mb-5">
    <a href="{{ route('admin.keuangan.bank-accounts.create') }}" class="btn-primary">+ Tambah Rekening</a>
</div>

<x-admin.table title="Rekening Bank">
    <thead>
        <tr>
            <th>Bank</th>
            <th>Nama Pemilik</th>
            <th>No. Rekening</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($accounts as $account)
        <tr>
            <td data-label="Bank">
                <div class="flex items-center gap-2">
                    @if($account->bank_logo)
                    <img src="{{ asset('storage/' . $account->bank_logo) }}" class="h-7 w-14 object-contain">
                    @endif
                    <span class="font-semibold">{{ $account->bank_name }}</span>
                </div>
            </td>
            <td data-label="Nama Pemilik">{{ $account->account_name }}</td>
            <td data-label="No. Rekening" class="font-mono">{{ $account->account_number }}</td>
            <td data-label="Status"><x-badge type="{{ $account->is_active ? 'success' : 'gray' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
            <td data-label="Aksi">
                <div class="action-group">
                    <a href="{{ route('admin.keuangan.bank-accounts.edit', $account->id) }}" class="btn-action-blue">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <form action="{{ route('admin.keuangan.bank-accounts.destroy', $account->id) }}" method="POST" data-confirm="Hapus rekening ini?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-action-red">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada rekening bank</td></tr>
        @endforelse
    </tbody>
</x-admin.table>
@endsection
