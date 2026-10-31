@props(['mobileCards' => false, 'title' => null, 'badge' => null])

<div {{ $attributes->merge(['class' => 'card overflow-hidden shadow-md']) }}>
    @if($title)
    <div class="card-header flex-wrap gap-2 py-3 admin-table-card-header">
        <h2 class="text-sm font-bold admin-table-card-title">{{ $title }}</h2>
        @if($badge)<x-badge type="info">{{ $badge }}</x-badge>@endif
    </div>
    @endif
    <div class="admin-table-wrapper">
        <table class="admin-table {{ $mobileCards ? 'admin-table-mobile-cards' : '' }}">
            {{ $slot }}
        </table>
    </div>
</div>
