@props(['column', 'label' => null])

@php
$label = $label ?? ucfirst(str_replace('_', ' ', $column));
$currentSort = request('sort', '');
$currentDir = request('dir', 'asc');
$isActive = $currentSort === $column;
$newDir = ($isActive && $currentDir === 'asc') ? 'desc' : 'asc';
$query = array_merge(request()->except(['sort', 'dir', 'page']), ['sort' => $column, 'dir' => $newDir]);
@endphp

<th {{ $attributes }}>
    <a href="{{ url()->current() . '?' . http_build_query($query) }}"
       class="inline-flex items-center gap-1 group hover:text-emerald-200 transition-colors">
        {{ $label }}
        @if($isActive)
            @if($currentDir === 'asc')
            <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
            @else
            <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            @endif
        @else
            <svg class="w-3.5 h-3.5 opacity-0 group-hover:opacity-60 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
        @endif
    </a>
</th>
