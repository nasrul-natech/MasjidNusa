@props([
    'href' => '#',
    'icon',
    'label',
    'active' => false,
    'type' => 'link',
    'featured' => false,
])

@if($type === 'button')
<button {{ $attributes->merge(['class' => 'bottom-nav-item' . ($active ? ' active' : '') . ($featured ? ' bottom-nav-item--featured' : ''), 'type' => 'button']) }}>
    @if($featured)
    <div class="bottom-nav-featured-icon {{ $active ? 'is-active' : '' }}">
        @include('components.icons.' . $icon, ['class' => 'w-6 h-6', 'filled' => true])
    </div>
    @else
    @include('components.icons.' . $icon, ['class' => 'w-5 h-5', 'filled' => $active])
    @endif
    <span class="text-[10px] font-medium leading-tight {{ $featured ? 'font-semibold' : '' }}">{{ $label }}</span>
</button>
@else
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'bottom-nav-item' . ($active ? ' active' : '') . ($featured ? ' bottom-nav-item--featured' : '')]) }}>
    @if($featured)
    <div class="bottom-nav-featured-icon {{ $active ? 'is-active' : '' }}">
        @include('components.icons.' . $icon, ['class' => 'w-6 h-6', 'filled' => true])
    </div>
    @else
    @include('components.icons.' . $icon, ['class' => 'w-5 h-5', 'filled' => $active])
    @endif
    <span class="text-[10px] font-medium leading-tight {{ $featured ? 'font-semibold' : '' }}">{{ $label }}</span>
</a>
@endif
