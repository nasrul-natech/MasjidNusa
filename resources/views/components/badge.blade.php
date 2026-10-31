@props(['type' => 'gray'])

@php
$classes = match($type) {
    'success' => 'bg-emerald-600 text-white ring-1 ring-emerald-700/20',
    'warning', 'yellow', 'orange' => 'bg-amber-500 text-amber-950 ring-1 ring-amber-600/25',
    'danger' => 'bg-red-600 text-white ring-1 ring-red-700/20',
    'info', 'blue' => 'bg-blue-600 text-white ring-1 ring-blue-700/20',
    'purple' => 'bg-purple-600 text-white ring-1 ring-purple-700/20',
    'teal' => 'bg-teal-600 text-white ring-1 ring-teal-700/20',
    'gray' => 'bg-slate-200 text-slate-800 ring-1 ring-slate-300/70',
    default => 'bg-slate-200 text-slate-800 ring-1 ring-slate-300/70',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold $classes"]) }}>
    {{ $slot }}
</span>
