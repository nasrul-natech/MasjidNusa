@extends('layouts.public')

@section('title', 'Notifikasi')

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-6 user-page-shell">
    <div class="max-w-lg mx-auto">
        <div class="user-page-hero mb-5">
            <p class="text-xs font-bold uppercase tracking-wider text-white/75">Akun</p>
            <h1 class="text-xl sm:text-2xl font-bold mt-1">Notifikasi</h1>
        </div>

        @forelse($notifications as $notif)
        <a href="{{ route('user.notifications.read', $notif->id) }}" class="flex items-start gap-3 p-4 mb-2 bg-white rounded-2xl border {{ !$notif->is_read ? 'border-emerald-200 bg-emerald-50/30' : 'border-gray-100' }} hover:shadow-sm transition-shadow">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 text-lg">
                @if($notif->category === 'payment') 💰
                @elseif($notif->category === 'activity') 📅
                @elseif($notif->category === 'security') 🔒
                @else 🔔
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 {{ !$notif->is_read ? 'text-emerald-900' : '' }}">{{ $notif->title }}</p>
                <p class="text-xs text-gray-600 mt-0.5">{{ $notif->body }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
            </div>
            @if(!$notif->is_read)
            <div class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0 mt-2"></div>
            @endif
        </a>
        @empty
        <div class="text-center py-16">
            <div class="text-5xl mb-3">🔔</div>
            <p class="text-gray-500 font-medium">Belum ada notifikasi</p>
            <p class="text-gray-400 text-sm mt-1">Notifikasi kegiatan dan donasi akan muncul di sini</p>
        </div>
        @endforelse

        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</div>
@endsection
