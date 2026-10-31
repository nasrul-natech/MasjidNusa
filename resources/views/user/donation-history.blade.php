@extends('layouts.public')

@section('title', 'Riwayat Donasi')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6 user-page-shell">
    <div class="max-w-lg mx-auto">
        <div class="user-page-hero mb-5">
            <p class="text-xs font-bold uppercase tracking-wider text-white/75">Akun</p>
            <h1 class="text-xl sm:text-2xl font-bold mt-1">Riwayat Donasi</h1>
        </div>

        @forelse($donations as $donation)
        <div class="card p-4 mb-3">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-xs text-gray-500 font-mono">{{ $donation->donation_code }}</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $donation->campaign?->title ?? 'Donasi Umum' }}</p>
                </div>
                <x-badge type="{{ $donation->status_color }}">{{ $donation->status_label }}</x-badge>
            </div>

            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500">Jumlah</p>
                    <p class="text-lg font-bold text-emerald-700">Rp {{ number_format($donation->amount, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Metode</p>
                    <p class="text-sm font-medium text-gray-700">{{ $donation->payment_method_type === 'manual' ? 'Transfer Manual' : 'Duitku' }}</p>
                </div>
            </div>

            <p class="text-xs text-gray-400 mt-2">{{ $donation->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }}</p>

            {{-- Upload proof if waiting --}}
            @if($donation->status === 'waiting_verification' && $donation->latestTransaction && !$donation->latestTransaction->proof)
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-amber-600 font-medium mb-2">⚠️ Upload bukti transfer untuk verifikasi</p>
                <form action="{{ route('user.donations.upload-proof', $donation->latestTransaction->id) }}" method="POST" enctype="multipart/form-data" class="flex gap-2">
                    @csrf
                    <input type="file" name="proof" accept="image/*" required class="flex-1 text-xs border border-gray-300 rounded-lg px-2 py-1.5">
                    <button type="submit" class="btn-primary text-xs px-3 py-1.5">Upload</button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-16">
            <div class="text-5xl mb-3">💝</div>
            <p class="text-gray-500 font-medium">Belum ada riwayat donasi</p>
            <a href="{{ route('donasi.index') }}" class="btn-primary mt-4 inline-block text-sm px-6 py-2.5">Mulai Donasi</a>
        </div>
        @endforelse

        {{ $donations->links() }}
    </div>
</div>
@endsection
