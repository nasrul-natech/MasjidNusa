@props([
    'compact' => false,
])

<div class="{{ $compact ? 'text-center text-emerald-400/80 text-xs space-y-1' : 'border-t border-emerald-700/50 mt-4 pt-4 text-center text-emerald-400 text-xs space-y-1.5' }}">
    <p>
        Platform
        <a href="{{ config('platform.developer_url') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-300 hover:text-white transition-colors">
            {{ config('platform.copyright') }}
        </a>
    </p>
    <p>
        Butuh sistem serupa?
        <a href="{{ config('platform.developer_url') }}" target="_blank" rel="noopener noreferrer" class="underline hover:text-white transition-colors">
            {{ config('platform.developer') }}
        </a>
        @unless($compact)
        ·
        <a href="{{ route('info-sistem') }}" class="underline hover:text-white transition-colors">Info Sistem</a>
        @endunless
    </p>
</div>
