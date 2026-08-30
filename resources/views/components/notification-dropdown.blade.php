@php
$unreadCount = auth()->check()
    ? \App\Models\UserNotification::where('user_id', auth()->id())->unread()->count()
    : 0;
$recentNotifs = auth()->check()
    ? \App\Models\UserNotification::where('user_id', auth()->id())->orderByDesc('created_at')->limit(5)->get()
    : collect();
@endphp

<div x-data="{ open: false }" class="relative">
    @auth
    <button @click="open = !open" class="relative p-2 rounded-xl hover:bg-gray-100 text-gray-600 focus:outline-none">
    @else
    <a href="{{ route('login') }}" class="relative p-2 rounded-xl hover:bg-gray-100 text-gray-600 focus:outline-none block">
    @endauth
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($unreadCount > 0)
        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    @auth
    </button>
    @else
    </a>
    @endauth

    @auth
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
        class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50"
        style="display: none;"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h4 class="font-semibold text-gray-900 text-sm">Notifikasi</h4>
            @if($unreadCount > 0)
            <form action="{{ route('user.notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Tandai semua dibaca</button>
            </form>
            @endif
        </div>

        <div class="max-h-72 overflow-y-auto">
            @forelse($recentNotifs as $notif)
            <a
                href="{{ route('user.notifications.read', $notif->id) }}"
                class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors {{ !$notif->is_read ? 'bg-emerald-50/50' : '' }}"
            >
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 text-sm">
                    @if($notif->category === 'payment') 💰
                    @elseif($notif->category === 'activity') 📅
                    @elseif($notif->category === 'security') 🔒
                    @else 🔔
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-800 {{ !$notif->is_read ? 'text-emerald-900' : '' }} line-clamp-1">{{ $notif->title }}</p>
                    <p class="text-xs text-gray-500 line-clamp-2 mt-0.5">{{ $notif->body }}</p>
                    <p class="text-[10px] text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
                @if(!$notif->is_read)
                <div class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0 mt-1.5"></div>
                @endif
            </a>
            @empty
            <div class="px-4 py-8 text-center">
                <p class="text-sm text-gray-500">Belum ada notifikasi</p>
            </div>
            @endforelse
        </div>

        <div class="border-t border-gray-100 px-4 py-3">
            <a href="{{ route('user.notifications.index') }}" @click="open = false" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                Lihat semua notifikasi →
            </a>
        </div>
    </div>
    @endauth
</div>
