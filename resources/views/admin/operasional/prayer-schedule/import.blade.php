@extends('layouts.admin.operasional')

@section('title', 'Import Jadwal Sholat')
@section('page-title', 'Import Jadwal Sholat')

@section('content')
<div class="max-w-xl">
    @if($locations->count() === 0)
    <div class="card p-8 text-center">
        <p class="text-gray-500 font-medium">Belum ada lokasi terdaftar</p>
        <p class="text-gray-400 text-sm mt-1">Tambahkan lokasi terlebih dahulu sebelum mengimpor jadwal</p>
        <a href="{{ route('admin.operasional.prayer-schedule.locations') }}" class="btn-primary text-sm mt-3 inline-block">+ Tambah Lokasi</a>
    </div>
    @else

    {{-- Fetch via API --}}
    <div class="card p-5 mb-5">
        <h3 class="font-semibold text-gray-900 mb-1">Ambil via API (Aladhan)</h3>
        <p class="text-xs text-gray-500 mb-4">
            Ambil jadwal langsung dari
            <a href="https://aladhan.com/prayer-times-api" target="_blank" class="text-emerald-600 underline">Aladhan API</a>
            — menggunakan metode perhitungan Kemenag RI, berdasarkan koordinat lokasi.
        </p>

        <form action="{{ route('admin.operasional.prayer-schedule.fetch-api') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="form-label">Lokasi <span class="text-red-500">*</span></label>
                <select name="location_id" class="form-input" required>
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->city_name }}, {{ $loc->province_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Bulan <span class="text-red-500">*</span></label>
                    <select name="month" class="form-input" required>
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Tahun <span class="text-red-500">*</span></label>
                    <select name="year" class="form-input" required>
                        @foreach(range(now()->year - 1, now()->year + 2) as $y)
                        <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-primary">Ambil dari API</button>
        </form>
    </div>

    {{-- Import via CSV --}}
    <div class="card p-5 mb-5">
        <h3 class="font-semibold text-gray-900 mb-1">Import dari File CSV</h3>
        <p class="text-xs text-gray-500 mb-4">Upload file CSV berisi jadwal sholat sesuai format Kemenag (alternatif jika API tidak tersedia)</p>

        <form action="{{ route('admin.operasional.prayer-schedule.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="form-label">Lokasi <span class="text-red-500">*</span></label>
                <select name="location_id" class="form-input" required>
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->city_name }}, {{ $loc->province_name }}</option>
                    @endforeach
                </select>
                @error('location_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Bulan <span class="text-red-500">*</span></label>
                    <select name="month" class="form-input" required>
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Tahun <span class="text-red-500">*</span></label>
                    <select name="year" class="form-input" required>
                        @foreach(range(now()->year - 1, now()->year + 2) as $y)
                        <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">File CSV <span class="text-red-500">*</span></label>
                <input type="file" name="csv_file" accept=".csv,.txt" class="form-input" required>
                @error('csv_file') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-secondary">Import CSV</button>
        </form>
    </div>

    {{-- CSV Format Guide --}}
    <div class="card p-4 bg-gray-50">
        <h4 class="font-medium text-gray-800 text-sm mb-2">Format CSV</h4>
        <p class="text-xs text-gray-600 mb-2">File CSV harus menggunakan separator titik koma (;) dengan format kolom:</p>
        <div class="bg-white rounded-lg p-3 font-mono text-xs text-gray-600 overflow-x-auto">
            <p class="text-gray-400 mb-1">Tanggal;Imsak;Subuh;Terbit;Dhuha;Dzuhur;Ashar;Maghrib;Isya</p>
            <p>1;04:23;04:33;05:50;06:15;11:55;15:12;17:53;19:04</p>
            <p>2;04:23;04:33;05:50;06:15;11:55;15:12;17:53;19:03</p>
            <p class="text-gray-400">...</p>
        </div>
        <p class="text-xs text-gray-500 mt-2">Data yang sudah ada untuk tanggal yang sama akan ditimpa (update).</p>
    </div>

    @endif

    <div class="mt-5">
        <a href="{{ route('admin.operasional.prayer-schedule.index') }}" class="btn-secondary text-sm">&larr; Kembali ke Jadwal</a>
    </div>
</div>
@endsection
