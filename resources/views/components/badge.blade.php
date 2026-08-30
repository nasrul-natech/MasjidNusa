@props(['type' => 'gray'])

@php
$classes = match($type) {
    'success' => 'bg-emerald-100 text-emerald-800',
    'warning', 'yellow', 'orange' => 'bg-amber-100 text-amber-800',
    'danger' => 'bg-red-100 text-red-800',
    'info', 'blue' => 'bg-blue-100 text-blue-800',
    'purple' => 'bg-purple-100 text-purple-800',
    default => 'bg-gray-100 text-gray-700',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold $classes"]) }}>
    {{ $slot }}
</span>
