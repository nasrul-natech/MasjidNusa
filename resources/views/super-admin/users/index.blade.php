@extends('layouts.super-admin')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
@php
    $pageUsers = $users->getCollection()
        ->filter(fn ($user) => $user->id !== auth()->id())
        ->mapWithKeys(fn ($user) => [$user->id => (bool) $user->is_active])
        ->all();
@endphp

<div x-data="{
    userActive: @js($pageUsers),
    selectableIds: @js(array_map('intval', array_keys($pageUsers))),
    selectedIds: [],
    tip: '',
    tipStyle: '',
    toggle(id, checked) {
        id = Number(id);
        if (checked) {
            if (!this.selectedIds.includes(id)) this.selectedIds = [...this.selectedIds, id];
        } else {
            this.selectedIds = this.selectedIds.filter(i => i !== id);
        }
    },
    toggleAll(checked) {
        this.selectedIds = checked ? [...this.selectableIds] : [];
    },
    isAllSelected() {
        return this.selectableIds.length > 0 && this.selectedIds.length === this.selectableIds.length;
    },
    isActive(id) {
        const value = this.userActive[id] ?? this.userActive[String(id)];
        return value === true || value === 1 || value === '1';
    },
    selectionMode() {
        if (!this.selectedIds.length) return 'none';
        const states = this.selectedIds.map(id => this.isActive(id));
        if (states.every(Boolean)) return 'all-active';
        if (states.every(s => !s)) return 'all-inactive';
        return 'mixed';
    },
    showTip(event, text) {
        const rect = event.currentTarget.getBoundingClientRect();
        this.tip = text;
        this.tipStyle = `left:${rect.left + rect.width / 2}px;top:${rect.top - 6}px`;
    },
    hideTip() {
        this.tip = '';
    },
    confirmThen(opts, run) {
        showSysModal({
            title: opts.title,
            body: opts.body,
            icon: opts.icon || '?',
            type: opts.type || 'warning',
            confirmText: opts.confirmText || 'Ya',
            cancelText: 'Batal',
            onConfirm: run,
        });
    },
    confirmBulk(action) {
        const mode = this.selectionMode();
        if (!this.selectedIds.length) return;
        if (action === 'deactivate' && mode !== 'all-active') return;
        if (action === 'activate' && mode !== 'all-inactive') return;
        const n = this.selectedIds.length;
        const copy = {
            deactivate: { title: 'Nonaktifkan user', body: `Nonaktifkan ${n} user yang masih aktif?`, icon: '⏸', type: 'warning', confirmText: 'Nonaktifkan' },
            activate: { title: 'Aktifkan user', body: `Aktifkan kembali ${n} user yang nonaktif?`, icon: '✓', type: 'success', confirmText: 'Aktifkan' },
            delete: { title: 'Hapus user', body: `Hapus permanen ${n} user terpilih? Tindakan ini tidak bisa dibatalkan.`, icon: '🗑', type: 'error', confirmText: 'Hapus' },
        }[action];
        this.confirmThen(copy, () => {
            const form = document.getElementById('user-bulk-form');
            form.querySelector('[name=action]').value = action;
            const container = document.getElementById('user-bulk-ids');
            container.innerHTML = '';
            this.selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = id;
                container.appendChild(input);
            });
            form.submit();
        });
    },
    confirmForm(form, opts) {
        this.confirmThen(opts, () => form.submit());
    }
}">

<form method="GET" class="admin-table-toolbar mb-3">
    <div class="admin-filter-grid">
        <div class="filter-field-wide">
            <label class="form-label text-xs">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / email..." class="form-input text-sm">
        </div>
        <div class="filter-field">
            <label class="form-label text-xs">Role</label>
            <select name="role" class="form-input text-sm">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-field">
            <label class="form-label text-xs">Status</label>
            <select name="status" class="form-input text-sm">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        @if(request('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif
        @if(request('dir'))
        <input type="hidden" name="dir" value="{{ request('dir') }}">
        @endif
        <div class="admin-filter-actions">
            <button type="submit" class="btn-primary text-sm h-[38px] px-4 flex-1 sm:flex-none">Cari</button>
            @if(request()->hasAny(['search', 'role', 'status', 'sort', 'dir']))
            <a href="{{ route('super-admin.users.index') }}" class="btn-ghost text-sm h-[38px] px-3 border border-gray-200 flex-1 sm:flex-none text-center">Reset</a>
            @endif
        </div>
    </div>
</form>

{{-- Toolbar: 25% status · 25% hapus · 50% tambah --}}
<div class="grid grid-cols-4 gap-2 mb-3 items-stretch">
    <button type="button" x-show="selectedIds.length > 0" x-cloak
        class="col-span-1 inline-flex items-center justify-center gap-1.5 rounded-xl text-sm font-semibold py-2.5 min-h-[42px] disabled:opacity-40 disabled:cursor-not-allowed"
        :disabled="selectionMode() === 'mixed'"
        :title="selectionMode() === 'mixed' ? 'Status campur — pilih user dengan status yang sama' : (selectionMode() === 'all-inactive' ? 'Aktifkan' : 'Nonaktifkan')"
        :class="selectionMode() === 'mixed' ? 'bg-gray-100 text-gray-400 opacity-40 cursor-not-allowed' : (selectionMode() === 'all-inactive' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-amber-50 text-amber-800 hover:bg-amber-100')"
        @click="selectionMode() === 'all-inactive' ? confirmBulk('activate') : confirmBulk('deactivate')">
        <svg x-show="selectionMode() === 'all-inactive'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <svg x-show="selectionMode() !== 'all-inactive'" x-cloak class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
        <span x-text="selectedIds.length"></span>
    </button>
    <button type="button" x-show="selectedIds.length > 0" x-cloak
        class="col-span-1 inline-flex items-center justify-center gap-1.5 rounded-xl text-sm font-semibold py-2.5 min-h-[42px] bg-red-50 text-red-700 hover:bg-red-100"
        title="Hapus"
        @click="confirmBulk('delete')">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        <span x-text="selectedIds.length"></span>
    </button>
    <a href="{{ route('super-admin.users.create') }}"
       class="btn-primary text-center py-2.5 min-h-[42px] flex items-center justify-center"
       :class="selectedIds.length > 0 ? 'col-span-2' : 'col-span-4'">
        + Tambah User
    </a>
</div>
<p x-show="selectionMode() === 'mixed'" x-cloak class="text-xs text-amber-700 mb-2">Status campur. Tombol aktif/nonaktif dimatikan sampai pilihan statusnya sama.</p>

@if($users->total() > 0)
<div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 mb-2 px-0.5">
    <span>Menampilkan <strong class="text-gray-700">{{ $users->firstItem() }}–{{ $users->lastItem() }}</strong> dari <strong class="text-gray-700">{{ $users->total() }}</strong> user</span>
    <span>Halaman <strong class="text-gray-700">{{ $users->currentPage() }}</strong> / {{ $users->lastPage() }}</span>
</div>
@endif

<form id="user-bulk-form" method="POST" action="{{ route('super-admin.users.bulk-action') }}" class="hidden">
    @csrf
    <input type="hidden" name="action" value="">
    <div id="user-bulk-ids"></div>
</form>

<x-admin.table>
    <thead>
        <tr>
            <th class="w-10 text-center px-2">
                <input type="checkbox" class="rounded border-white/50 text-emerald-500 focus:ring-emerald-400"
                    @change="toggleAll($event.target.checked)"
                    :checked="isAllSelected()"
                    aria-label="Pilih semua">
            </th>
            <th class="w-11 text-center px-2">No</th>
            <x-admin.sortable-th column="name" label="Pengguna" class="px-3" />
            <th class="px-3">Role</th>
            <x-admin.sortable-th column="is_active" label="Status" class="px-3" />
            <x-admin.sortable-th column="created_at" label="Bergabung" class="px-3" />
            <th class="px-3 w-[1%] whitespace-nowrap">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
        <tr>
            <td class="text-center px-2">
                @if($user->id !== auth()->id())
                <input type="checkbox" class="rounded text-emerald-600 border-gray-300"
                    @change="toggle({{ $user->id }}, $event.target.checked)"
                    :checked="selectedIds.includes({{ $user->id }})"
                    aria-label="Pilih {{ $user->name }}">
                @endif
            </td>
            <td class="text-center text-xs text-gray-500 font-medium px-2">{{ $users->firstItem() + $loop->index }}</td>
            <td class="px-3">
                <div class="flex items-center gap-2 min-w-[140px]">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-xs font-bold shrink-0">{{ substr($user->name, 0, 1) }}</div>
                    <div class="min-w-0">
                        <p class="font-medium text-sm truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                    </div>
                </div>
            </td>
            <td class="px-3 whitespace-nowrap">
                @foreach($user->roles as $role)
                <x-badge type="info">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</x-badge>
                @endforeach
            </td>
            <td class="px-3 whitespace-nowrap">
                <x-badge type="{{ $user->is_active ? 'success' : 'gray' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
            </td>
            <td class="text-xs text-gray-500 whitespace-nowrap px-3">{{ $user->created_at->format('d M Y') }}</td>
            <td class="px-3">
                <div class="action-group">
                    <a href="{{ route('super-admin.users.edit', $user->id) }}" class="btn-action-blue w-8 h-8 p-0 justify-center" title="Edit" aria-label="Edit"
                        @mouseenter="showTip($event, 'Edit')" @mouseleave="hideTip()" @focus="showTip($event, 'Edit')" @blur="hideTip()">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('super-admin.users.toggle-active', $user->id) }}" method="POST">
                        @csrf
                        <button type="button"
                            class="{{ $user->is_active ? 'btn-action-amber' : 'btn-action-green' }} w-8 h-8 p-0 justify-center"
                            title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                            aria-label="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                            @mouseenter="showTip($event, '{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}')" @mouseleave="hideTip()" @focus="showTip($event, '{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}')" @blur="hideTip()"
                            @click="confirmForm($el.closest('form'), {{ \Illuminate\Support\Js::from([
                                'title' => $user->is_active ? 'Nonaktifkan user' : 'Aktifkan user',
                                'body' => $user->is_active ? 'Nonaktifkan '.$user->name.'?' : 'Aktifkan kembali '.$user->name.'?',
                                'icon' => $user->is_active ? '⏸' : '✓',
                                'type' => $user->is_active ? 'warning' : 'success',
                                'confirmText' => $user->is_active ? 'Nonaktifkan' : 'Aktifkan',
                            ]) }})">
                            @if($user->is_active)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('super-admin.users.reset-password', $user->id) }}" method="POST">
                        @csrf
                        <button type="button" class="btn-action-amber w-8 h-8 p-0 justify-center" title="Reset password" aria-label="Reset password"
                            @mouseenter="showTip($event, 'Reset password')" @mouseleave="hideTip()" @focus="showTip($event, 'Reset password')" @blur="hideTip()"
                            @click="confirmForm($el.closest('form'), {{ \Illuminate\Support\Js::from([
                                'title' => 'Reset password',
                                'body' => 'Reset password '.$user->name.'?',
                                'icon' => '🔑',
                                'type' => 'warning',
                                'confirmText' => 'Reset',
                            ]) }})">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </button>
                    </form>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('super-admin.users.destroy', $user->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="button" class="btn-action-red w-8 h-8 p-0 justify-center" title="Hapus" aria-label="Hapus"
                            @mouseenter="showTip($event, 'Hapus')" @mouseleave="hideTip()" @focus="showTip($event, 'Hapus')" @blur="hideTip()"
                            @click="confirmForm($el.closest('form'), {{ \Illuminate\Support\Js::from([
                                'title' => 'Hapus user',
                                'body' => 'Hapus permanen '.$user->name.'? Tindakan ini tidak bisa dibatalkan.',
                                'icon' => '🗑',
                                'type' => 'error',
                                'confirmText' => 'Hapus',
                            ]) }})">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-8 text-gray-500">Belum ada user</td></tr>
        @endforelse
    </tbody>
</x-admin.table>

<div class="mt-4">{{ $users->withQueryString()->links() }}</div>

<div x-show="tip" x-cloak class="fixed z-50 -translate-x-1/2 -translate-y-full pointer-events-none rounded-md bg-gray-900 px-2 py-1 text-[10px] font-medium text-white shadow" :style="tipStyle" x-text="tip"></div>

</div>
@endsection
