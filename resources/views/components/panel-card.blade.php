@props([
    'title',
    'description' => null,
    'tone' => 'emerald',
])

@php
$headerTone = match($tone) {
    'sky', 'blue' => 'card-header-soft-sky',
    'amber', 'orange' => 'card-header-soft-amber',
    'indigo' => 'card-header-soft-indigo',
    'purple', 'violet' => 'card-header-soft-purple',
    'rose', 'red' => 'card-header-soft-rose',
    'teal' => 'card-header-soft-teal',
    'slate', 'gray' => 'card-header-soft-slate',
    default => 'card-header-soft-emerald',
};
@endphp

<div {{ $attributes->merge(['class' => 'card overflow-hidden shadow-md']) }}>
    <div class="card-header {{ $headerTone }} flex-wrap gap-2">
        <div class="min-w-0">
            <h2 class="text-sm font-bold panel-card-title">{{ $title }}</h2>
            @if($description)
                <p class="mt-1 text-xs panel-card-desc">{{ $description }}</p>
            @endif
        </div>
        @isset($badge)
            <div class="shrink-0 flex flex-wrap gap-2">{{ $badge }}</div>
        @endisset
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
