@extends('layouts.super-admin')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="flex flex-wrap justify-between gap-3 mb-5">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..." class="form-input w-56 text-sm">
        <select name="role" class="form-input w-auto text-sm" onchange="this.form.submit()">
            <option value="">Semua Role</option>
            @foreach($roles as $role)
            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary px-4 text-sm">Cari</button>
    </form>
    <a href="{{ route('super-admin.users.create') }}" class="btn-primary">+ Tambah User</a>
</div>

<div class="card overflow-hidden">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Role</th>
                <th>Status</th>
                <th>Bergabung</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-xs font-bold shrink-0">{{ substr($user->name, 0, 1) }}</div>
                        <div>
                            <p class="font-medium text-sm">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td>
                    @foreach($user->roles as $role)
                    <x-badge type="info">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</x-badge>
                    @endforeach
                </td>
                <td><x-badge type="{{ $user->is_active ? 'success' : 'gray' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                <td class="text-xs text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    <div class="action-group">
                        <a href="{{ route('super-admin.users.edit', $user->id) }}" class="btn-action-blue">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                        <form action="{{ route('super-admin.users.toggle-active', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="{{ $user->is_active ? 'btn-action-amber' : 'btn-action-green' }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $user->is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }}"/></svg>
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        <form action="{{ route('super-admin.users.reset-password', $user->id) }}" method="POST" onsubmit="return confirm('Reset password user ini?')">
                            @csrf
                            <button type="submit" class="btn-action-amber">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                Reset PW
                            </button>
                        </form>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('super-admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-action-red">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada user</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->withQueryString()->links() }}</div>
@endsection
