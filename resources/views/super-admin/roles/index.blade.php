@extends('layouts.super-admin')

@section('title', 'Role & Permission')
@section('page-title', 'Manajemen Role & Permission')

@section('content')
<div class="space-y-6">
    @foreach($roles as $role)
    <div class="card overflow-hidden" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
        {{-- Role Header --}}
        <button @click="open = !open" class="w-full card-header flex items-center justify-between cursor-pointer hover:bg-gray-50 transition">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold
                    {{ $role->name === 'super_admin' ? 'bg-red-500' : ($role->name === 'admin_operasional' ? 'bg-emerald-500' : ($role->name === 'admin_keuangan' ? 'bg-blue-500' : ($role->name === 'admin_media_it' ? 'bg-purple-500' : 'bg-gray-500'))) }}">
                    {{ strtoupper(substr($role->name, 0, 2)) }}
                </div>
                <div class="text-left">
                    <h3 class="font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</h3>
                    <p class="text-xs text-gray-500">{{ $role->permissions->count() }} permission aktif</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        {{-- Permission Form --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2">
            <form action="{{ route('super-admin.roles.assign', $role->id) }}" method="POST">
                @csrf
                <div class="p-5 space-y-5">
                    @foreach($permissions as $group => $groupPerms)
                    <div class="bg-gray-50 rounded-xl p-4" x-data="{
                        checkAll() {
                            const boxes = this.$el.querySelectorAll('input[type=checkbox][name=\'permissions[]\']');
                            const allChecked = [...boxes].every(b => b.checked);
                            boxes.forEach(b => b.checked = !allChecked);
                        }
                    }">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full
                                    {{ in_array($group, ['manage', 'admin']) ? 'bg-red-400' : (in_array($group, ['view', 'read']) ? 'bg-blue-400' : (in_array($group, ['create', 'edit', 'update']) ? 'bg-emerald-400' : 'bg-gray-400')) }}">
                                </span>
                                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">{{ ucfirst($group) }}</h4>
                                <span class="text-[10px] text-gray-400">({{ $groupPerms->count() }})</span>
                            </div>
                            <button type="button" @click="checkAll()" class="text-[10px] text-emerald-600 hover:text-emerald-800 font-medium">Toggle Semua</button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                            @foreach($groupPerms as $permission)
                            <label class="flex items-center gap-2 cursor-pointer group py-1 px-2 rounded-lg hover:bg-white transition">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                       class="rounded text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0"
                                    {{ $role->permissions->contains('name', $permission->name) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700 group-hover:text-gray-900">
                                    {{ str_replace($group . '_', '', $permission->name) }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <p class="text-xs text-gray-400">
                        <span class="font-mono">{{ $role->name }}</span>
                    </p>
                    <button type="submit" class="btn-primary text-sm px-5">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>

{{-- Legend --}}
<div class="card p-4 mt-6 bg-gray-50">
    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Keterangan Warna Role</h4>
    <div class="flex flex-wrap gap-4 text-xs text-gray-600">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-500"></span> Super Admin</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-500"></span> Admin Operasional</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-500"></span> Admin Keuangan</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-purple-500"></span> Admin Media IT</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-500"></span> Lainnya</span>
    </div>
</div>
@endsection
