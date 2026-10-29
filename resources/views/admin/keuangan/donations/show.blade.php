@extends('layouts.admin.keuangan')

@section('title', 'Detail Donasi')
@section('page-title', 'Detail Donasi')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.keuangan.donations.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Kembali ke Daftar Donasi</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <div class="md:col-span-2 space-y-4">
        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Informasi Donasi</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <dt class="text-gray-500">Kode Donasi</dt>
                <dd class="font-mono font-semibold">{{ $donation->donation_code }}</dd>

                <dt class="text-gray-500">Nama Donatur</dt>
                <dd class="font-medium">{{ $donation->donor_name }}</dd>

                <dt class="text-gray-500">Email</dt>
                <dd>{{ $donation->donor_email }}</dd>

                @if($donation->donor_phone)
                <dt class="text-gray-500">Telepon</dt>
                <dd>{{ $donation->donor_phone }}</dd>
                @endif

                <dt class="text-gray-500">Kampanye</dt>
                <dd>{{ $donation->campaign?->title ?? 'Donasi Umum' }}</dd>

                <dt class="text-gray-500">Jumlah</dt>
                <dd class="font-bold text-emerald-700 text-base">Rp {{ number_format($donation->amount, 0, ',', '.') }}</dd>

                <dt class="text-gray-500">Status</dt>
                <dd>
                    @php
                        $statusColors = ['pending'=>'yellow','waiting_verification'=>'warning','verified'=>'success','paid'=>'success','rejected'=>'danger','expired'=>'gray'];
                        $statusLabels = ['pending'=>'Menunggu','waiting_verification'=>'Perlu Verifikasi','verified'=>'Terverifikasi','paid'=>'Lunas','rejected'=>'Ditolak','expired'=>'Kedaluwarsa'];
                    @endphp
                    <x-badge type="{{ $statusColors[$donation->status] ?? 'gray' }}">{{ $statusLabels[$donation->status] ?? $donation->status }}</x-badge>
                </dd>

                <dt class="text-gray-500">Anonim</dt>
                <dd>{{ $donation->is_anonymous ? 'Ya' : 'Tidak' }}</dd>

                <dt class="text-gray-500">Tanggal</dt>
                <dd>{{ $donation->created_at->format('d M Y H:i') }}</dd>
            </dl>

            @if($donation->message)
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-1">Pesan</p>
                <p class="text-sm">{{ $donation->message }}</p>
            </div>
            @endif
        </div>

        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Riwayat Transaksi</h3>
            @forelse($donation->transactions as $transaction)
            <div class="border border-gray-100 rounded-xl p-3 mb-3">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-mono text-xs text-gray-500">{{ $transaction->transaction_code }}</p>
                        <p class="text-sm font-medium">{{ $transaction->paymentMethod?->name ?? $transaction->payment_method }}</p>
                    </div>
                    <x-badge type="{{ $transaction->status === 'paid' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'yellow') }}">{{ ucfirst($transaction->status) }}</x-badge>
                </div>
                <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d M Y H:i') }}</p>

                @if($transaction->proof)
                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-2">Bukti Pembayaran</p>
                    @if($transaction->proof->image_url)
                    <a href="{{ $transaction->proof->image_url }}" target="_blank">
                        <img src="{{ $transaction->proof->image_url }}" class="h-32 object-cover rounded-lg hover:opacity-80 transition-opacity">
                    </a>
                    @else
                    <p class="text-xs text-gray-400">Bukti pembayaran tidak tersedia</p>
                    @endif
                    @if(!$transaction->proof->verified_at)
                    <div class="flex gap-2 mt-3">
                        <form action="{{ route('admin.keuangan.verify-payment.approve', $donation->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary text-xs px-4 py-1.5">Verifikasi</button>
                        </form>
                        <form action="{{ route('admin.keuangan.verify-payment.reject', $donation->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-danger text-xs px-4 py-1.5" data-confirm="Tolak pembayaran ini?">Tolak</button>
                        </form>
                    </div>
                    @else
                    <p class="text-xs text-emerald-600 mt-2">✓ Diverifikasi {{ $transaction->proof->updated_at->format('d M Y H:i') }}</p>
                    @endif
                </div>
                @endif
            </div>
            @empty
            <p class="text-sm text-gray-500">Belum ada transaksi</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-4">
        @if($donation->user)
        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Data Akun</h3>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold">{{ substr($donation->user->name, 0, 1) }}</div>
                <div>
                    <p class="font-medium text-sm">{{ $donation->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $donation->user->email }}</p>
                </div>
            </div>
            <a href="{{ route('super-admin.users.edit', $donation->user->id) }}" class="text-xs text-blue-600 hover:underline">Lihat profil</a>
        </div>
        @endif

        @if($donation->campaign)
        <div class="card p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Kampanye</h3>
            <p class="font-medium text-sm">{{ $donation->campaign->title }}</p>
            <div class="mt-2">
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min($donation->campaign->progress_percent, 100) }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($donation->campaign->progress_percent, 1) }}% terkumpul</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
