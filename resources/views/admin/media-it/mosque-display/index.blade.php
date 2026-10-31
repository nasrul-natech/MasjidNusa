@extends('layouts.admin.media-it')
@section('title', 'Pengaturan TV')
@section('page-title', 'Pengaturan TV')
@section('content')
@php
    $defaults = \App\Services\MosqueDisplayService::DEFAULTS;
    $draft = array_replace($defaults, $profile->draft ?? []);
    $tabs = ['identity' => 'Identitas', 'layout' => 'Tata Letak', 'media' => 'Media', 'content' => 'Konten', 'prayer' => 'Salat & Iqamah', 'publish' => 'Publikasi'];
    $tz = $locations->firstWhere('id', $draft['prayer_location_id'])?->timezone() ?? config('app.timezone');
    $mediaCount = $profile->items->count();
    $announcementLines = old('announcements');
    if (! is_array($announcementLines)) {
        $announcementLines = is_array($draft['announcements'] ?? null) ? $draft['announcements'] : [];
        $announcementLines = array_values(array_filter($announcementLines, fn ($line) => is_string($line) && trim($line) !== ''));
        if ($announcementLines === [] && trim((string) ($draft['announcement'] ?? '')) !== '') {
            $announcementLines = [trim((string) $draft['announcement'])];
        }
    }
    if ($announcementLines === []) {
        $announcementLines = [''];
    }
    $announcementLines = array_values(array_slice(array_map(fn ($line) => (string) $line, $announcementLines), 0, \App\Services\MosqueDisplayService::ANNOUNCEMENT_LIMIT));
@endphp
<div class="space-y-4 text-sm" x-data="{
    tabs: ['identity', 'layout', 'media', 'content', 'prayer', 'publish'],
    tab: 'identity', copied: false, restoredTab: @js(old('_tab', 'identity')),
    readHash() { const key = window.location.hash.slice(1); this.tab = this.tabs.includes(key) ? key : (this.tabs.includes(this.restoredTab) ? this.restoredTab : 'identity'); },
    moveTab(event) {
        const button = event.target.closest('[role=tab]');
        if (!button || !['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const buttons = Array.from(button.parentElement.querySelectorAll('[role=tab]'));
        const index = buttons.indexOf(button);
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? buttons.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + buttons.length) % buttons.length;
        buttons[next].click(); buttons[next].focus();
    }
}" x-init="readHash(); $watch('tab', value => history.replaceState(null, '', '#' + value))" @hashchange.window="readHash()">
    <div class="card overflow-hidden">
        <div class="card-header bg-emerald-50">
            <h2 class="text-sm font-semibold text-gray-800">Layar masjid</h2>
            <a class="btn-primary" target="_blank" rel="noopener" href="{{ route('mosque-display.index') }}">Buka TV / Fullscreen</a>
        </div>
        <div class="card-body flex flex-wrap items-center gap-2">
            <x-badge type="info">Versi {{ $profile->version }}</x-badge>
            <x-badge type="warning">Draft</x-badge>
            @if($profile->published_at)
                <x-badge type="success">Terbit {{ $profile->published_at->format('d/m/Y H:i') }}</x-badge>
            @else
                <x-badge>Belum terbit</x-badge>
            @endif
            <x-badge type="gray">{{ $mediaCount }} media</x-badge>
        </div>
    </div>
    @if($errors->any())
        <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700" role="alert">
            <p>Perubahan belum disimpan. Periksa isian berikut:</p>
            <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <nav class="card overflow-hidden" role="presentation">
        <div class="card-body flex flex-wrap gap-2" role="tablist" aria-label="Kategori pengaturan TV" @keydown="moveTab($event)">
            @foreach($tabs as $key => $label)
                <button type="button" id="tv-tab-{{ $key }}" role="tab" aria-controls="tv-panel-{{ $key }}" :aria-selected="tab === '{{ $key }}'" :tabindex="tab === '{{ $key }}' ? 0 : -1" @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'btn-primary' : 'btn-secondary'">{{ $label }}</button>
            @endforeach
        </div>
    </nav>

    {{-- Keep every settings field enabled; reveal invalid fields before browser focus. --}}
    <form method="POST" action="{{ route('admin.media-it.mosque-display.update') }}" class="space-y-4" x-cloak x-show="['identity', 'layout', 'content', 'prayer'].includes(tab)" novalidate @submit="if (!$el.checkValidity()) { $event.preventDefault(); const invalid = $el.querySelector(':invalid'); tab = invalid.closest('[role=tabpanel]').id.replace('tv-panel-', ''); $nextTick(() => invalid.reportValidity()); }">
        @csrf
        @method('PUT')
        <input type="hidden" name="_tab" :value="tab" value="identity">
        <section id="tv-panel-identity" role="tabpanel" aria-labelledby="tv-tab-identity" tabindex="0" x-show="tab === 'identity'" class="card overflow-hidden">
            <div class="card-header bg-emerald-50">
                <h2 class="text-sm font-semibold text-gray-800">Identitas Layar</h2>
                <x-badge type="success">Layar</x-badge>
            </div>
            <div class="card-body">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <img src="{{ app(\App\Services\MosqueDisplayService::class)->logoUrl() }}" alt="Logo masjid" class="h-14 w-24 rounded-xl bg-gray-50 object-contain p-1">
                    <div class="min-w-0 flex-1">
                        <label for="tv-title" class="form-label">Judul <span class="text-red-500">*</span></label>
                        <input id="tv-title" class="form-input" name="title" maxlength="160" required value="{{ old('title', $draft['title']) }}">
                    </div>
                </div>
            </div>
        </section>
        <section id="tv-panel-layout" role="tabpanel" aria-labelledby="tv-tab-layout" tabindex="0" x-show="tab === 'layout'" class="card overflow-hidden">
            <div class="card-header bg-sky-50">
                <h2 class="text-sm font-semibold text-gray-800">Tata Letak &amp; Warna</h2>
                <x-badge type="info">Tampilan</x-badge>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div><label for="tv-layout" class="form-label">Tata letak</label><select id="tv-layout" name="layout" class="form-input">@foreach(['focus' => 'Fokus media', 'split' => 'Media dan kegiatan', 'immersive' => 'Layar penuh'] as $key => $label)<option value="{{ $key }}" @selected(old('layout', $draft['layout']) === $key)>{{ $label }}</option>@endforeach</select></div>
                    <div><label for="tv-media-fit" class="form-label">Ukuran foto / video</label><select id="tv-media-fit" name="media_fit" class="form-input"><option value="contain" @selected(old('media_fit', $draft['media_fit']) === 'contain')>Utuh, sesuai kartu</option><option value="cover" @selected(old('media_fit', $draft['media_fit']) === 'cover')>Penuhi kartu (boleh terpotong)</option></select><p class="mt-1 text-xs text-gray-500">Penuhi kartu memotong sisi yang tidak muat. Tiap tata letak punya rasio berbeda — lihat tab Media.</p></div>
                    <div><label for="tv-theme" class="form-label">Tema</label><select id="tv-theme" name="theme" class="form-input">@foreach(\App\Services\MosqueDisplayService::THEMES as $key => $label)<option value="{{ $key }}" @selected(old('theme', $draft['theme']) === $key)>{{ $label }}</option>@endforeach</select></div>
                    <div><label for="tv-accent" class="form-label">Warna aksen</label><input id="tv-accent" class="form-input h-12" type="color" name="accent" value="{{ old('accent', $draft['accent']) }}"></div>
                    <div><label for="tv-decoration" class="form-label">Dekorasi</label><select id="tv-decoration" class="form-input" name="decoration">@foreach(\App\Services\MosqueDisplayService::DECORATIONS as $key => $label)<option value="{{ $key }}" @selected(old('decoration', $draft['decoration']) === $key)>{{ $label }}</option>@endforeach</select></div>
                    <div><label for="tv-transition" class="form-label">Transisi media (md)</label><input id="tv-transition" class="form-input" type="number" name="transition_ms" min="200" max="2000" step="1" required value="{{ old('transition_ms', $draft['transition_ms']) }}"></div>
                </div>
            </div>
        </section>
        <section id="tv-panel-content" role="tabpanel" aria-labelledby="tv-tab-content" tabindex="0" x-show="tab === 'content'" class="card overflow-hidden">
            <div class="card-header bg-amber-50">
                <h2 class="text-sm font-semibold text-gray-800">Jadwal &amp; Pengumuman</h2>
                <x-badge type="warning">Konten</x-badge>
            </div>
            <div class="card-body space-y-3">
                <div><label for="tv-location" class="form-label">Lokasi jadwal salat</label><select id="tv-location" name="prayer_location_id" class="form-input"><option value="">Jadwal harian manual ({{ config('app.timezone') }})</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('prayer_location_id', $draft['prayer_location_id']) == $location->id)>{{ $location->full_name }} ({{ $location->timezone() }})</option>@endforeach</select></div>
                <div class="space-y-3" x-data="{ lines: @js(collect($announcementLines)->map(fn ($text, $index) => ['id' => $index + 1, 'text' => $text])->values()), nextId: {{ count($announcementLines) + 1 }}, limit: {{ \App\Services\MosqueDisplayService::ANNOUNCEMENT_LIMIT }} }">
                    <div class="flex items-center justify-between gap-2">
                        <p class="form-label mb-0">Pengumuman teks</p>
                        <button type="button" class="btn-secondary" @click="if (lines.length < limit) lines.push({ id: nextId++, text: '' })" :disabled="lines.length >= limit">Tambah pengumuman</button>
                    </div>
                    <p class="text-xs text-gray-500">Bisa lebih dari satu. Di TV teks berjalan berurutan, dan tiap pengumuman dipisah ikon logo masjid.</p>
                    <template x-for="(line, index) in lines" :key="line.id">
                        <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-3">
                            <div class="mb-1 flex items-center justify-between gap-2">
                                <label class="text-xs font-semibold text-amber-950" :for="'tv-announcement-' + line.id">Pengumuman <span x-text="index + 1"></span></label>
                                <button type="button" class="text-xs font-semibold text-red-600 hover:text-red-800" x-show="lines.length > 1" @click="lines.splice(index, 1)">Hapus</button>
                            </div>
                            <textarea class="form-input bg-white" rows="2" maxlength="{{ \App\Services\MosqueDisplayService::ANNOUNCEMENT_LENGTH }}" :id="'tv-announcement-' + line.id" :name="'announcements[' + index + ']'" x-model="line.text" :placeholder="index === 0 ? 'Contoh: Kajian subuh besok setelah salat' : 'Pengumuman berikutnya'"></textarea>
                        </div>
                    </template>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="min-w-0"><label for="tv-announcement-seconds" class="form-label">Putaran (detik)</label><input id="tv-announcement-seconds" class="form-input px-2 py-2" type="number" name="announcement_seconds" min="10" max="180" step="1" required value="{{ old('announcement_seconds', $draft['announcement_seconds']) }}"></div>
                    <div class="min-w-0"><label for="tv-announcement-size" class="form-label">Ukuran teks</label><select id="tv-announcement-size" class="form-input px-2 py-2" name="announcement_size">@foreach(['normal' => 'Normal', 'large' => 'Besar', 'extra' => 'Ekstra besar'] as $key => $label)<option value="{{ $key }}" @selected(old('announcement_size', $draft['announcement_size']) === $key)>{{ $label }}</option>@endforeach</select></div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer"><input type="hidden" name="show_activities" value="0"><input type="checkbox" name="show_activities" value="1" class="rounded text-emerald-600" @checked(old('show_activities', $draft['show_activities']))><span>Tampilkan kegiatan terbit</span></label>
            </div>
        </section>
        <section id="tv-panel-prayer" role="tabpanel" aria-labelledby="tv-tab-prayer" tabindex="0" x-show="tab === 'prayer'" class="card overflow-hidden">
            <div class="card-header bg-purple-50">
                <h2 class="text-sm font-semibold text-gray-800">Salat &amp; Iqamah</h2>
                <x-badge type="purple">Jadwal</x-badge>
            </div>
            <div class="card-body space-y-3">
                <label class="flex items-center gap-2 cursor-pointer"><input type="hidden" name="prayer_mode_enabled" value="0"><input type="checkbox" name="prayer_mode_enabled" value="1" class="rounded text-emerald-600" @checked(old('prayer_mode_enabled', $draft['prayer_mode_enabled']))><span>Mode salat otomatis</span></label>
                <div class="grid grid-cols-3 gap-2">
                    <div class="min-w-0"><label for="tv-adhan-minutes" class="form-label">Adzan (menit)</label><input id="tv-adhan-minutes" class="form-input px-2 py-2" type="number" name="adhan_minutes" min="1" max="15" step="1" required value="{{ old('adhan_minutes', $draft['adhan_minutes']) }}" aria-describedby="tv-prayer-sequence"></div>
                    <div class="min-w-0"><label for="tv-iqamah-minutes" class="form-label">Iqamah (menit)</label><input id="tv-iqamah-minutes" class="form-input px-2 py-2" type="number" name="iqamah_minutes" min="0" max="30" step="1" required value="{{ old('iqamah_minutes', $draft['iqamah_minutes']) }}" aria-describedby="tv-prayer-sequence"></div>
                    <div class="min-w-0"><label for="tv-prayer-minutes" class="form-label">Salat (menit)</label><input id="tv-prayer-minutes" class="form-input px-2 py-2" type="number" name="prayer_minutes" min="1" max="60" step="1" required value="{{ old('prayer_minutes', $draft['prayer_minutes']) }}" aria-describedby="tv-prayer-sequence"></div>
                </div>
                <p id="tv-prayer-sequence" class="text-gray-500">Adzan, hitung mundur iqamah, lalu jam redup selama salat. Audio adzan tidak diputar dari TV.</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['adhan' => 'Preview Adzan', 'iqamah' => 'Preview Iqamah', 'prayer' => 'Preview Salat'] as $phase => $label)
                    <a href="{{ route('admin.media-it.mosque-display.preview', ['phase' => $phase]) }}" class="btn-secondary" target="_blank" rel="noopener">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </section>
        <button type="submit" class="btn-primary">Simpan draft pengaturan</button>
    </form>

    <section id="tv-panel-media" role="tabpanel" aria-labelledby="tv-tab-media" tabindex="0" x-cloak x-show="tab === 'media'" class="space-y-4">
        <div class="card overflow-hidden">
            <div class="card-header flex-wrap gap-2 bg-blue-50">
                <h2 class="text-sm font-semibold text-gray-800">Playlist draft</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <x-badge type="info">{{ $mediaCount }}/50</x-badge>
                    <x-badge>JPEG PNG WebP</x-badge>
                    <x-badge type="danger">YouTube senyap</x-badge>
                </div>
            </div>
            <div class="card-body border-t border-gray-100 text-gray-600 leading-relaxed space-y-3">
                @php
                    $mediaFrames = \App\Services\MosqueDisplayService::MEDIA_FRAMES;
                    $activeLayout = $draft['layout'] ?? 'focus';
                @endphp
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Petunjuk resolusi foto dan YouTube</h3>
                    <p class="mt-1">Kartu media di TV berbeda di tiap tata letak. Foto yang sama (misalnya 16:9) akan <strong class="text-gray-800">terpotong</strong> jika dipaksakan mengisi kartu yang lebih lebar atau lebih sempit. Unggah sesuai tata letak yang sedang dipakai. Batas unggah {{ \App\Services\MosqueDisplayService::PHOTO_UPLOAD_MAX_SIDE }} px per sisi (maks. 12 megapiksel, 5 MB); sistem menyimpan hingga {{ \App\Services\MosqueDisplayService::PHOTO_STORED_MAX }} px tanpa mengubah rasio.</p>
                </div>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                    @foreach($mediaFrames as $key => $frame)
                        <div class="rounded-xl border p-3 {{ $activeLayout === $key ? 'border-emerald-400 bg-emerald-50' : 'border-gray-200 bg-gray-50' }}">
                            <p class="text-xs font-bold uppercase tracking-wide {{ $activeLayout === $key ? 'text-emerald-800' : 'text-gray-500' }}">{{ $frame['label'] }}@if($activeLayout === $key) · sedang dipakai @endif</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $frame['width'] }}×{{ $frame['height'] }}</p>
                            <div class="mt-2 overflow-hidden rounded-md bg-gray-900" style="aspect-ratio: {{ $frame['width'] }} / {{ $frame['height'] }}; max-height: 4.5rem;"></div>
                            <p class="mt-2 text-xs leading-relaxed">{{ $frame['note'] }}</p>
                            <p class="mt-1 text-xs leading-relaxed text-gray-500">{{ $frame['youtube'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @forelse($profile->items as $item)
            @php $isVideo = $item->type === 'youtube'; @endphp
            <div class="card overflow-hidden">
                <div class="card-header {{ $isVideo ? 'bg-red-50' : 'bg-blue-50' }}">
                    <div class="flex min-w-0 flex-1 items-center gap-2">
                        <x-badge :type="$isVideo ? 'danger' : 'info'">{{ $isVideo ? 'YouTube' : 'Foto' }}</x-badge>
                        <h3 class="min-w-0 break-words text-sm font-semibold text-gray-800">{{ $item->title }}</h3>
                    </div>
                    <x-badge :type="$item->is_active ? 'success' : 'gray'">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                </div>
                <div class="card-body space-y-3 p-4">
                    @if($item->photo)
                        @php $previewFrame = \App\Services\MosqueDisplayService::MEDIA_FRAMES[$draft['layout'] ?? 'focus'] ?? \App\Services\MosqueDisplayService::MEDIA_FRAMES['focus']; @endphp
                        <img src="{{ route('admin.media-it.mosque-display.photo', $item->photo) }}" alt="{{ $item->title }}" class="w-full rounded-xl bg-gray-900 object-contain" style="aspect-ratio: {{ $previewFrame['width'] }} / {{ $previewFrame['height'] }}; max-height: 10rem;">
                    @endif
                    <form method="POST" action="{{ route('admin.media-it.mosque-display.items.update', $item) }}" class="space-y-3">
                        <input type="hidden" name="_tab" value="media">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                            <div class="col-span-2 min-w-0"><label for="tv-item-{{ $item->id }}-title" class="form-label">Judul</label><input id="tv-item-{{ $item->id }}-title" name="title" required maxlength="160" value="{{ $item->title }}" class="form-input px-2 py-2"></div>
                            <div class="min-w-0"><label for="tv-item-{{ $item->id }}-sort" class="form-label">Urutan</label><input id="tv-item-{{ $item->id }}-sort" type="number" name="sort_order" min="0" max="1000" value="{{ $item->sort_order }}" required class="form-input px-2 py-2"></div>
                            <div class="min-w-0"><label for="tv-item-{{ $item->id }}-duration" class="form-label">Durasi (detik)</label><input id="tv-item-{{ $item->id }}-duration" type="number" name="duration_seconds" min="5" max="300" value="{{ $item->duration_seconds }}" required class="form-input px-2 py-2"></div>
                            <div class="min-w-0 lg:col-span-2"><label for="tv-item-{{ $item->id }}-start" class="form-label">Mulai</label><input id="tv-item-{{ $item->id }}-start" type="datetime-local" name="starts_at" value="{{ $item->starts_at?->copy()->setTimezone($tz)->format('Y-m-d\TH:i') }}" class="form-input px-2 py-2"></div>
                            <div class="min-w-0 lg:col-span-2"><label for="tv-item-{{ $item->id }}-end" class="form-label">Selesai</label><input id="tv-item-{{ $item->id }}-end" type="datetime-local" name="ends_at" value="{{ $item->ends_at?->copy()->setTimezone($tz)->format('Y-m-d\TH:i') }}" class="form-input px-2 py-2"></div>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <label class="flex items-center gap-2 cursor-pointer"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600" @checked($item->is_active)><span>Aktif</span></label>
                            <button type="submit" class="btn-secondary">Simpan item</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.media-it.mosque-display.items.destroy', $item) }}" onsubmit="return confirm('Hapus item dari draft?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action-red">Hapus draft</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-gray-500">Playlist masih kosong.</div></div>
        @endforelse
        <div class="card overflow-hidden">
            <div class="card-header bg-emerald-50">
                <h3 class="text-sm font-semibold text-gray-800">Tambah item</h3>
                <x-badge type="success">Baru</x-badge>
            </div>
            <form method="POST" enctype="multipart/form-data" action="{{ route('admin.media-it.mosque-display.items.store') }}" class="card-body space-y-3 p-4">
                <input type="hidden" name="_tab" value="media">
                @csrf
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="min-w-0 lg:col-span-2"><label for="tv-new-type" class="form-label">Jenis</label><select id="tv-new-type" name="type" class="form-input px-2 py-2"><option value="photo">Foto</option><option value="youtube">YouTube</option></select></div>
                    <div class="min-w-0 lg:col-span-2"><label for="tv-new-title" class="form-label">Judul</label><input id="tv-new-title" name="title" required maxlength="160" class="form-input px-2 py-2"></div>
                    <div class="min-w-0 rounded-xl border border-blue-100 bg-blue-50 p-3 lg:col-span-2"><label for="tv-new-photo" class="form-label">Foto</label><input id="tv-new-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="form-input bg-white px-2 py-2" aria-describedby="tv-photo-resolution-help"><p id="tv-photo-resolution-help" class="mt-1 text-gray-500">Sesuaikan rasio dengan tata letak di atas. Rasio asli dipertahankan; mode "Penuhi kartu" yang memotong.</p></div>
                    <div class="min-w-0 rounded-xl border border-gray-200 bg-red-50 p-3 lg:col-span-2"><label for="tv-new-youtube" class="form-label">URL YouTube</label><input id="tv-new-youtube" type="url" name="youtube_url" maxlength="300" placeholder="https://www.youtube.com/watch?v=..." class="form-input bg-white px-2 py-2" aria-describedby="tv-youtube-resolution-help"><p id="tv-youtube-resolution-help" class="mt-1 text-gray-500">YouTube selalu 16:9 (ideal 1920×1080). Di Fokus media dan Media+kegiatan video ditampilkan utuh di dalam kartu, bukan dipotong mengikuti foto.</p></div>
                    <div class="min-w-0"><label for="tv-new-sort" class="form-label">Urutan</label><input id="tv-new-sort" name="sort_order" type="number" min="0" max="1000" value="0" required class="form-input px-2 py-2"></div>
                    <div class="min-w-0"><label for="tv-new-duration" class="form-label">Durasi (detik)</label><input id="tv-new-duration" name="duration_seconds" type="number" min="5" max="300" value="15" required class="form-input px-2 py-2"></div>
                    <div class="min-w-0"><label for="tv-new-start" class="form-label">Mulai</label><input id="tv-new-start" type="datetime-local" name="starts_at" class="form-input px-2 py-2"></div>
                    <div class="min-w-0"><label for="tv-new-end" class="form-label">Selesai</label><input id="tv-new-end" type="datetime-local" name="ends_at" class="form-input px-2 py-2"></div>
                </div>
                <p class="text-gray-500">Isi foto atau URL YouTube. Waktu tayang mengikuti {{ $tz }}.</p>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="flex items-center gap-2 cursor-pointer"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600" checked><span>Aktif</span></label>
                    <button type="submit" class="btn-primary">Tambah ke draft</button>
                </div>
            </form>
        </div>
    </section>

    <section id="tv-panel-publish" role="tabpanel" aria-labelledby="tv-tab-publish" tabindex="0" x-cloak x-show="tab === 'publish'" class="card overflow-hidden">
        <div class="card-header bg-teal-50">
            <h2 class="text-sm font-semibold text-gray-800">Pratinjau &amp; Publikasi</h2>
            <x-badge type="info">TV</x-badge>
        </div>
        <div class="card-body space-y-3">
            <div>
                <label for="tv-public-url" class="form-label">URL publik layar TV</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input id="tv-public-url" readonly value="{{ route('mosque-display.index') }}" class="form-input min-w-0 sm:flex-1" @click="$el.select()">
                    <button type="button" class="btn-secondary" @click="navigator.clipboard ? navigator.clipboard.writeText(document.getElementById('tv-public-url').value).then(() => copied = true).catch(() => { copied = false; document.getElementById('tv-public-url').focus(); document.getElementById('tv-public-url').select(); }) : (document.getElementById('tv-public-url').focus(), document.getElementById('tv-public-url').select())">Salin URL</button>
                </div>
                <p class="mt-1 text-emerald-700" role="status" x-text="copied ? 'URL disalin' : ''"></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="btn-secondary" target="_blank" rel="noopener" href="{{ route('admin.media-it.mosque-display.preview') }}">Pratinjau draft</a>
                <a class="btn-secondary" target="_blank" rel="noopener" href="{{ route('mosque-display.index') }}">Buka TV / Fullscreen</a>
                <form method="POST" action="{{ route('admin.media-it.mosque-display.publish') }}" onsubmit="return confirm('Terbitkan seluruh draft dan playlist ke TV?')">@csrf<button type="submit" class="btn-primary">Terbitkan ke TV</button></form>
            </div>
        </div>
    </section>
</div>
@endsection
