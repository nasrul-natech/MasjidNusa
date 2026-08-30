@extends('layouts.admin.operasional')
@php($doa = $doa ?? null)

@section('title', $doa ? 'Edit Doa' : 'Tambah Doa')
@section('page-title', $doa ? 'Edit Doa' : 'Tambah Doa Baru')

@section('content')
<form action="{{ $doa ? route('admin.operasional.doa.update', $doa->id) : route('admin.operasional.doa.store') }}"
      method="POST">
    @csrf
    @if($doa) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-5 space-y-4">
                <div>
                    <label class="form-label">Judul Doa <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $doa->title ?? '') }}" class="form-input text-lg font-semibold" required placeholder="Contoh: Doa Sebelum Makan">
                    @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Teks Arab</label>
                    <textarea name="arabic" class="form-input text-right text-lg leading-loose" rows="4" dir="rtl"
                        style="font-family: 'Traditional Arabic', 'Scheherazade New', 'Amiri', serif; font-size: 1.4rem; line-height: 2.2;"
                        placeholder="بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ">{{ old('arabic', $doa->arabic ?? '') }}</textarea>
                </div>

                <div>
                    <label class="form-label">Transliterasi Latin</label>
                    <textarea name="latin" class="form-input italic" rows="3" placeholder="Bismillāhirraḥmānirraḥīm">{{ old('latin', $doa->latin ?? '') }}</textarea>
                </div>

                <div>
                    <label class="form-label">Terjemahan <span class="text-red-500">*</span></label>
                    <textarea name="translation" class="form-input" rows="3" required placeholder="Dengan nama Allah Yang Maha Pengasih lagi Maha Penyayang">{{ old('translation', $doa->translation ?? '') }}</textarea>
                    @error('translation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Extended content (HTML / Text toggle) --}}
            <div class="card p-5 space-y-4" x-data="{ mode: '{{ old('_content_mode', ($doa && $doa->content_html) ? 'html' : 'text') }}' }">
                <div class="flex items-center justify-between">
                    <label class="form-label mb-0">Penjelasan / Keterangan</label>
                    <div class="flex bg-gray-100 rounded-lg p-0.5 text-xs">
                        <button type="button" @click="mode='text'" :class="mode==='text' ? 'bg-white shadow text-gray-900' : 'text-gray-500'" class="px-3 py-1 rounded-md font-medium transition-colors">Teks</button>
                        <button type="button" @click="mode='html'" :class="mode==='html' ? 'bg-white shadow text-gray-900' : 'text-gray-500'" class="px-3 py-1 rounded-md font-medium transition-colors">HTML</button>
                    </div>
                </div>

                <div x-show="mode==='text'">
                    <textarea name="content_text" class="form-input" rows="6" placeholder="Penjelasan doa dalam format teks biasa...">{{ old('content_text', $doa->content_text ?? '') }}</textarea>
                </div>
                <div x-show="mode==='html'">
                    <textarea name="content_html" class="form-input font-mono text-sm" rows="8" placeholder="<p>Penjelasan doa dalam format HTML...</p>">{{ old('content_html', $doa->content_html ?? '') }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">Gunakan tag HTML standar seperti &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, dll.</p>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="card p-5 space-y-4">
                <h3 class="font-semibold text-gray-800 text-sm">Pengaturan</h3>

                <div>
                    <label class="form-label">Kategori <span class="text-red-500">*</span></label>
                    <select name="doa_category_id" class="form-input" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('doa_category_id', $doa->doa_category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('doa_category_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="draft" {{ old('status', $doa->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="publish" {{ old('status', $doa->status ?? '') === 'publish' ? 'selected' : '' }}>Publish</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Sumber</label>
                    <input type="text" name="source" value="{{ old('source', $doa->source ?? '') }}" class="form-input text-sm" placeholder="Contoh: HR. Muslim no. 2713">
                    <p class="text-xs text-gray-400 mt-1">Hadits, ayat Al-Quran, atau kitab rujukan</p>
                </div>

                <div>
                    <label class="form-label">Urutan</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $doa->sort_order ?? 0) }}" class="form-input" min="0">
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <button type="submit" class="btn-primary w-full">Simpan</button>
                <a href="{{ route('admin.operasional.doa.index') }}" class="btn-secondary w-full text-center">Batal</a>
            </div>
        </div>
    </div>
</form>
@endsection
