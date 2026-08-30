@props([
    'href',
    'icon',
    'label',
    'color' => 'emerald',
    'active' => false,
])

@php
$colors = [
    'red'     => ['bg' => 'bg-red-100',     'icon' => 'text-red-500'],
    'blue'    => ['bg' => 'bg-blue-100',    'icon' => 'text-blue-500'],
    'teal'    => ['bg' => 'bg-teal-100',    'icon' => 'text-teal-600'],
    'indigo'  => ['bg' => 'bg-indigo-100',  'icon' => 'text-indigo-600'],
    'amber'   => ['bg' => 'bg-amber-100',   'icon' => 'text-amber-500'],
    'emerald' => ['bg' => 'bg-emerald-100', 'icon' => 'text-emerald-600'],
    'purple'  => ['bg' => 'bg-purple-100',  'icon' => 'text-purple-600'],
];
$c = $colors[$color] ?? $colors['emerald'];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex flex-col items-center gap-1.5 p-2.5 rounded-2xl transition-all snap-start ' . ($active ? 'bg-emerald-50 ring-1 ring-emerald-200' : 'bg-white border border-gray-100 hover:shadow-md')]) }}>
    <div class="w-10 h-10 {{ $c['bg'] }} rounded-xl flex items-center justify-center">
        @include('components.icons.' . $icon, ['class' => 'w-5 h-5 ' . $c['icon'], 'filled' => $active])
    </div>
    <span class="text-[10px] font-semibold {{ $active ? 'text-emerald-700' : 'text-gray-700' }} leading-tight text-center">{{ $label }}</span>
</a>
