@extends('layouts.super-admin')

@section('title', 'Pengaturan Umum')
@section('page-title', 'Pengaturan Umum')

@section('content')
<div class="card p-5 lg:p-6 mb-6 bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-100">
    <div class="flex items-start gap-4">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
            @include('components.icons.cog', ['class' => 'w-5 h-5 text-emerald-700'])
        </div>
        <div class="flex-1">
            <h3 class="font-bold text-gray-900 mb-1">Info Sistem Platform</h3>
            <p class="text-sm text-gray-600 mb-3">Informasi platform MasjidNusa — tidak dapat diubah melalui panel ini.</p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                <div>
                    <dt class="text-gray-500">Platform</dt>
                    <dd class="font-semibold text-gray-900">{{ config('platform.name') }} v{{ config('platform.version') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Pengembang</dt>
                    <dd>
                        <a href="{{ config('platform.developer_url') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-emerald-700 hover:underline">
                            {{ config('platform.developer') }}
                        </a>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-gray-500">Hak Cipta</dt>
                    <dd class="font-semibold text-gray-900">{{ config('platform.copyright') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

<form action="{{ route('super-admin.settings.update') }}" method="POST">
    @csrf

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @forelse($settings as $group => $groupSettings)
        <div class="card p-5 lg:p-6">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-100">
                <div class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center">
                    @include('components.icons.cog', ['class' => 'w-4 h-4 text-gray-600'])
                </div>
                <h3 class="font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $group) }}</h3>
            </div>
            <div class="space-y-4">
                @foreach($groupSettings as $setting)
                <div>
                    <label class="form-label">{{ $setting->label ?? $setting->key }}</label>
                    @if($setting->type === 'textarea')
                    <textarea name="{{ $setting->key }}" class="form-input" rows="3">{{ old($setting->key, $setting->value) }}</textarea>
                    @elseif($setting->type === 'boolean')
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                        <input type="hidden" name="{{ $setting->key }}" value="0">
                        <input type="checkbox" name="{{ $setting->key }}" value="1" class="rounded text-emerald-600" {{ old($setting->key, $setting->value) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                    @else
                    <input type="text" name="{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}" class="form-input">
                    @endif
                    @if($setting->description)
                    <p class="text-xs text-gray-400 mt-1">{{ $setting->description }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="card p-8 text-center text-gray-500 xl:col-span-2">Belum ada pengaturan yang dikonfigurasi</div>
        @endforelse
    </div>

    @if($settings->isNotEmpty())
    <div class="mt-6">
        <button type="submit" class="btn-primary px-6">Simpan Pengaturan</button>
    </div>
    @endif
</form>
@endsection
