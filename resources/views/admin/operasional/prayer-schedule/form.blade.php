@extends('layouts.admin.operasional')
@php($schedule = $schedule ?? new \App\Models\PrayerSchedule)

@section('title', $schedule->exists ? 'Edit Jadwal Sholat' : 'Tambah Jadwal Sholat')
@section('page-title', $schedule->exists ? 'Edit Jadwal Sholat' : 'Tambah Jadwal Sholat')

@section('content')
<div class="max-w-md">
    <form action="{{ $schedule->exists ? route('admin.operasional.prayer-schedule.update', $schedule->id) : route('admin.operasional.prayer-schedule.store') }}" method="POST" class="space-y-4">
        @csrf
        @if($schedule->exists) @method('PUT') @endif

        <div class="card p-5 space-y-4">
            <div>
                <label class="form-label">Nama Sholat <span class="text-red-500">*</span></label>
                <input type="text" name="prayer_name" value="{{ old('prayer_name', $schedule->prayer_name ?? '') }}" class="form-input" placeholder="Contoh: Subuh" required>
            </div>

            <div>
                <label class="form-label">Waktu <span class="text-red-500">*</span></label>
                <input type="time" name="time" value="{{ old('time', $schedule->exists ? \Carbon\Carbon::parse($schedule->time)->format('H:i') : '') }}" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Tipe</label>
                <select name="type" class="form-input">
                    <option value="daily" {{ old('type', $schedule->type ?? 'daily') === 'daily' ? 'selected' : '' }}>Harian (Fardhu)</option>
                    <option value="jumat" {{ old('type', $schedule->type ?? '') === 'jumat' ? 'selected' : '' }}>Jum'at</option>
                    <option value="special" {{ old('type', $schedule->type ?? '') === 'special' ? 'selected' : '' }}>Khusus</option>
                </select>
            </div>

            <div>
                <label class="form-label">Catatan</label>
                <input type="text" name="notes" value="{{ old('notes', $schedule->notes ?? '') }}" class="form-input" placeholder="Opsional">
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600" {{ old('is_active', $schedule->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Aktif</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6">Simpan</button>
            <a href="{{ route('admin.operasional.prayer-schedule.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
