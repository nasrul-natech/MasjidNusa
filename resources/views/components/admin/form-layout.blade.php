@props(['aside' => null])

<div {{ $attributes->merge(['class' => 'grid grid-cols-1 xl:grid-cols-5 gap-6']) }}>
    <div class="xl:col-span-3 space-y-6">
        {{ $slot }}
    </div>
    @if($aside)
    <div class="xl:col-span-2 space-y-4">
        {{ $aside }}
    </div>
    @endif
</div>
