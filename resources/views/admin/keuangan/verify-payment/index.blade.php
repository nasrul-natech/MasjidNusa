@extends('layouts.admin.keuangan')

@section('title', 'Verifikasi Pembayaran')
@section('page-title', 'Verifikasi Pembayaran Manual')

@section('content')
@if($pendingDonations->isEmpty())
<div class="card p-10 text-center">
    <div class="text-4xl mb-3">✓</div>
    <p class="font-semibold text-gray-700">Tidak ada pembayaran yang menunggu verifikasi</p>
    <p class="text-sm text-gray-500 mt-1">Semua pembayaran telah diproses</p>
</div>
@else
<div class="space-y-4">
    @foreach($pendingDonations as $donation)
    <div class="card p-4">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $donation->donor_name }}</p>
                        <p class="text-xs text-gray-500">{{ $donation->donation_code }}</p>
                    </div>
                    <p class="font-bold text-emerald-700 text-lg">Rp {{ number_format($donation->amount, 0, ',', '.') }}</p>
                </div>
                <p class="text-sm text-gray-600">{{ $donation->campaign?->title ?? 'Donasi Umum' }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $donation->created_at->format('d M Y H:i') }}</p>
            </div>

            @if($donation->latestTransaction?->proof)
            <div class="shrink-0">
                <p class="text-xs text-gray-500 mb-1">Bukti Transfer</p>
                @if($donation->latestTransaction->proof->image_url)
                <a href="{{ $donation->latestTransaction->proof->image_url }}" target="_blank" class="block">
                    <img src="{{ $donation->latestTransaction->proof->image_url }}" class="h-28 w-28 object-cover rounded-xl hover:opacity-80 transition-opacity border border-gray-200">
                </a>
                @else
                <div class="h-28 w-28 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                @endif
            </div>
            @endif

            <div class="flex md:flex-col gap-2 shrink-0 justify-end">
                <form action="{{ route('admin.keuangan.verify-payment.approve', $donation->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full btn-primary text-sm">✓ Verifikasi</button>
                </form>
                <form action="{{ route('admin.keuangan.verify-payment.reject', $donation->id) }}" method="POST" data-confirm="Tolak pembayaran ini?">
                    @csrf
                    <button type="submit" class="w-full btn-danger text-sm">✕ Tolak</button>
                </form>
                <a href="{{ route('admin.keuangan.donations.show', $donation->id) }}" class="text-xs text-center text-blue-600 hover:underline">Detail</a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-4">{{ $pendingDonations->links() }}</div>
@endif
@endsection
