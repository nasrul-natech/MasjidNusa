@extends('layouts.public')

@section('title', $campaign->title)

@section('content')
<div class="max-w-screen-xl mx-auto">
    {{-- Banner --}}
    @if($campaign->banner)
    <div class="relative">
        <img src="{{ $campaign->banner_url }}" alt="{{ $campaign->title }}" class="w-full h-48 sm:h-64 object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
        <a href="{{ route('donasi.index') }}" class="absolute top-4 left-4 w-9 h-9 bg-black/30 rounded-xl flex items-center justify-center text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
    </div>
    @endif

    <div class="px-4 py-5 max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-5">{{ $campaign->title }}</h1>

        {{-- Progress Card --}}
        <div class="card p-5 mb-5">
            <div class="flex justify-between items-end mb-3">
                <div>
                    <p class="text-xs text-gray-500">Terkumpul</p>
                    <p class="text-2xl font-bold text-emerald-700">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Target</p>
                    <p class="text-sm font-semibold text-gray-900">Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="h-3 bg-gray-100 rounded-full overflow-hidden mb-2">
                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $campaign->progress_percent }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
                <span>{{ $campaign->progress_percent }}% tercapai</span>
                @if($campaign->end_date)
                <span>Berakhir {{ $campaign->end_date->locale('id')->isoFormat('D MMM Y') }}</span>
                @endif
            </div>
        </div>

        {{-- Donate Button --}}
        <div class="mb-6">
            <x-payment-popup :campaign="$campaign" />
        </div>

        {{-- Description --}}
        @if($campaign->content)
        <div class="prose prose-sm max-w-none text-gray-700 mb-6">
            {!! $campaign->content !!}
        </div>
        @elseif($campaign->description)
        <p class="text-gray-700 text-sm leading-relaxed mb-6">{{ $campaign->description }}</p>
        @endif

        {{-- Recent Donors --}}
        @if($donors->count())
        <div class="card p-5">
            <h3 class="font-bold text-gray-900 mb-4">Donatur Terbaru</h3>
            <div class="space-y-3">
                @foreach($donors as $donor)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 text-sm font-bold text-emerald-700">
                        {{ substr($donor->is_anonymous ? 'A' : $donor->donor_name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ $donor->is_anonymous ? 'Hamba Allah' : $donor->donor_name }}</p>
                        @if($donor->message)
                        <p class="text-xs text-gray-500 truncate">{{ $donor->message }}</p>
                        @endif
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-emerald-700">Rp {{ number_format($donor->amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400">{{ $donor->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
