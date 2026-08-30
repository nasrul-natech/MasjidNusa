@extends('layouts.admin.operasional')

@section('title', 'Lokasi Jadwal Sholat')
@section('page-title', 'Kelola Lokasi')

@section('content')
<div class="max-w-2xl">
    {{-- Add Location Form --}}
    <div class="card p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-1">Tambah Lokasi Baru</h3>
        <p class="text-xs text-gray-500 mb-4">Pilih provinsi dan kota — koordinat otomatis terisi untuk perhitungan waktu sholat via Aladhan API (metode Kemenag RI)</p>

        <form action="{{ route('admin.operasional.prayer-schedule.locations.store') }}" method="POST" class="space-y-3">
            @csrf

            <div>
                <label class="form-label text-xs">Provinsi <span class="text-red-500">*</span></label>
                <select id="province-select" class="form-input text-sm" required>
                    <option value="">-- Pilih Provinsi --</option>
                </select>
            </div>

            <div>
                <label class="form-label text-xs">Kota <span class="text-red-500">*</span></label>
                <select id="city-select" class="form-input text-sm" disabled required>
                    <option value="">-- Pilih provinsi dulu --</option>
                </select>
            </div>

            <input type="hidden" name="province_name" id="province-hidden">
            <input type="hidden" name="city_name" id="city-hidden">
            <input type="hidden" name="latitude" id="lat-hidden">
            <input type="hidden" name="longitude" id="lng-hidden">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label text-xs text-gray-400">Latitude</label>
                    <input type="text" id="lat-display" class="form-input text-xs bg-gray-50 text-gray-500" readonly>
                </div>
                <div>
                    <label class="form-label text-xs text-gray-400">Longitude</label>
                    <input type="text" id="lng-display" class="form-input text-xs bg-gray-50 text-gray-500" readonly>
                </div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_default" value="1" class="rounded text-emerald-600">
                <span class="text-sm text-gray-700">Jadikan lokasi default</span>
            </label>

            <button type="submit" id="submit-btn" class="btn-primary text-sm" disabled>Tambah Lokasi</button>
        </form>
    </div>

    {{-- Location List --}}
    <div class="space-y-3">
        @forelse($locations as $location)
        <div class="card p-4 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-gray-900">{{ $location->city_name }}</h3>
                    @if($location->is_default)
                    <x-badge type="success">Default</x-badge>
                    @endif
                </div>
                <p class="text-xs text-gray-500">{{ $location->province_name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $location->prayer_times_count }} data jadwal
                    @if($location->latitude && $location->longitude)
                        · {{ $location->latitude }}, {{ $location->longitude }}
                    @else
                        · <span class="text-amber-500">Koordinat belum diisi</span>
                    @endif
                </p>
            </div>
            <div class="action-group">
                @unless($location->is_default)
                <form action="{{ route('admin.operasional.prayer-schedule.locations.default', $location->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-action-green">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Set Default
                    </button>
                </form>
                @endunless
                <form action="{{ route('admin.operasional.prayer-schedule.locations.destroy', $location->id) }}" method="POST" onsubmit="return confirm('Hapus lokasi ini beserta semua jadwalnya?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-action-red">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="card p-8 text-center text-gray-500">Belum ada lokasi terdaftar</div>
        @endforelse
    </div>

    <div class="mt-5">
        <a href="{{ route('admin.operasional.prayer-schedule.index') }}" class="btn-secondary text-sm">&larr; Kembali ke Jadwal</a>
    </div>
</div>

<script>
(function () {
    const citiesUrl = @json(route('admin.operasional.prayer-schedule.api.cities'));
    const provincesUrl = @json(route('admin.operasional.prayer-schedule.api.provinces'));

    const provinceSelect = document.getElementById('province-select');
    const citySelect     = document.getElementById('city-select');
    const provinceHidden = document.getElementById('province-hidden');
    const cityHidden     = document.getElementById('city-hidden');
    const latHidden      = document.getElementById('lat-hidden');
    const lngHidden      = document.getElementById('lng-hidden');
    const latDisplay     = document.getElementById('lat-display');
    const lngDisplay     = document.getElementById('lng-display');
    const submitBtn      = document.getElementById('submit-btn');

    fetch(provincesUrl)
        .then(r => r.json())
        .then(json => {
            (json.data || []).forEach(prov => {
                const opt = document.createElement('option');
                opt.value = prov;
                opt.textContent = prov;
                provinceSelect.appendChild(opt);
            });
        });

    provinceSelect.addEventListener('change', function () {
        const prov = this.value;
        provinceHidden.value = prov;
        cityHidden.value = '';
        latHidden.value = '';
        lngHidden.value = '';
        latDisplay.value = '';
        lngDisplay.value = '';
        submitBtn.disabled = true;

        if (!prov) {
            citySelect.innerHTML = '<option value="">-- Pilih provinsi dulu --</option>';
            citySelect.disabled = true;
            return;
        }

        citySelect.innerHTML = '<option value="">Memuat...</option>';
        citySelect.disabled = true;

        fetch(citiesUrl + '?province=' + encodeURIComponent(prov))
            .then(r => r.json())
            .then(json => {
                citySelect.innerHTML = '<option value="">-- Pilih Kota --</option>';
                (json.data || []).forEach(city => {
                    const opt = document.createElement('option');
                    opt.value = JSON.stringify(city);
                    opt.textContent = city.name;
                    citySelect.appendChild(opt);
                });
                citySelect.disabled = false;
            });
    });

    citySelect.addEventListener('change', function () {
        if (!this.value) {
            cityHidden.value = '';
            latHidden.value = '';
            lngHidden.value = '';
            latDisplay.value = '';
            lngDisplay.value = '';
            submitBtn.disabled = true;
            return;
        }

        try {
            const city = JSON.parse(this.value);
            cityHidden.value = city.name;
            latHidden.value = city.lat;
            lngHidden.value = city.lng;
            latDisplay.value = city.lat;
            lngDisplay.value = city.lng;
            submitBtn.disabled = false;
        } catch (e) {
            submitBtn.disabled = true;
        }
    });
})();
</script>
@endsection
