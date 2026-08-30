@props(['mobileCards' => true])

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    <div class="admin-table-wrapper">
        <table class="admin-table {{ $mobileCards ? 'admin-table-mobile-cards' : '' }}">
            {{ $slot }}
        </table>
    </div>
</div>
