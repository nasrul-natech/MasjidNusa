@props(['action' => null, 'placeholder' => 'Cari...'])

<form method="GET" action="{{ $action ?? url()->current() }}" {{ $attributes->merge(['class' => 'admin-table-toolbar']) }}>
    <div class="admin-filter-grid">
        {{ $slot }}
        <div class="admin-filter-actions">
            <button type="submit" class="btn-primary text-sm h-[38px] px-4 flex-1 sm:flex-none">
                <svg class="w-3.5 h-3.5 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'category', 'sort', 'dir', 'role', 'action', 'date', 'user_id', 'filter', 'month']))
            <a href="{{ url()->current() }}" class="btn-ghost text-sm h-[38px] px-3 border border-gray-200 flex-1 sm:flex-none text-center">Reset</a>
            @endif
        </div>
    </div>
</form>
