@props(['label', 'value', 'icon' => null, 'iconName' => null, 'color' => 'emerald', 'change' => null, 'changeType' => 'up', 'href' => null, 'variant' => 'default'])

@php
$filledStyles = match($color) {
    'blue'    => ['gradient' => 'from-blue-500 to-blue-700',    'iconBg' => 'bg-white/20'],
    'amber'   => ['gradient' => 'from-amber-500 to-orange-600', 'iconBg' => 'bg-white/20'],
    'red'     => ['gradient' => 'from-red-500 to-red-700',      'iconBg' => 'bg-white/20'],
    'purple'  => ['gradient' => 'from-purple-500 to-purple-700','iconBg' => 'bg-white/20'],
    'teal'    => ['gradient' => 'from-teal-500 to-teal-700',    'iconBg' => 'bg-white/20'],
    'indigo'  => ['gradient' => 'from-indigo-500 to-indigo-700','iconBg' => 'bg-white/20'],
    default   => ['gradient' => 'from-emerald-500 to-emerald-700','iconBg' => 'bg-white/20'],
};
$softStyles = match($color) {
    'blue'    => 'bg-blue-100 text-blue-700',
    'amber'   => 'bg-amber-100 text-amber-700',
    'red'     => 'bg-red-100 text-red-700',
    'purple'  => 'bg-purple-100 text-purple-700',
    'teal'    => 'bg-teal-100 text-teal-700',
    'indigo'  => 'bg-indigo-100 text-indigo-700',
    default   => 'bg-emerald-100 text-emerald-700',
};
$isFilled = $variant === 'filled';
@endphp

@if($isFilled)
<div {{ $attributes->merge(['class' => 'rounded-2xl bg-gradient-to-br ' . $filledStyles['gradient'] . ' p-5 text-white shadow-lg ' . ($href ? 'hover:shadow-xl hover:scale-[1.02] transition-all cursor-pointer' : '')]) }}>
    @if($href)<a href="{{ $href }}" class="block">@endif
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-white/80 truncate">{{ $label }}</p>
            <p class="text-2xl lg:text-3xl font-bold mt-1 leading-tight">{{ $value }}</p>
            @if($change)
            <p class="text-xs mt-2 text-white/70">{{ $changeType === 'up' ? '↑' : '↓' }} {{ $change }}</p>
            @endif
        </div>
        @if($iconName || $icon)
        <div class="w-12 h-12 rounded-xl {{ $filledStyles['iconBg'] }} flex items-center justify-center shrink-0">
            @if($iconName)
                @include('components.icons.' . $iconName, ['class' => 'w-6 h-6 text-white'])
            @else
                {!! $icon !!}
            @endif
        </div>
        @endif
    </div>
    @if($href)</a>@endif
</div>
@else
<div {{ $attributes->merge(['class' => 'card p-5 ' . ($href ? 'hover:shadow-md transition-shadow' : '')]) }}>
    @if($href)<a href="{{ $href }}" class="block">@endif
    <div class="flex items-center gap-4">
        @if($iconName || $icon)
        <div class="w-12 h-12 rounded-xl {{ $softStyles }} flex items-center justify-center flex-shrink-0">
            @if($iconName)
                @include('components.icons.' . $iconName, ['class' => 'w-6 h-6'])
            @else
                {!! $icon !!}
            @endif
        </div>
        @endif
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-500 truncate">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $value }}</p>
            @if($change)
            <p class="text-xs mt-1 {{ $changeType === 'up' ? 'text-emerald-600' : 'text-red-500' }}">{{ $changeType === 'up' ? '↑' : '↓' }} {{ $change }}</p>
            @endif
        </div>
    </div>
    @if($href)</a>@endif
</div>
@endif
