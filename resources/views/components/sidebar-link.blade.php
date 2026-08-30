@props(['href', 'active' => false, 'icon' => 'circle'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'sidebar-link' . ($active ? ' active' : '')]) }}>
    @include('components.icons.' . $icon, ['class' => 'w-4 h-4 flex-shrink-0'])
    <span>{{ $slot }}</span>
</a>
