@extends('layouts.super-admin')
@php($template = $template ?? new \App\Models\EmailTemplate)

@section('title', $template->exists ? 'Edit Template' : 'Tambah Template')
@section('page-title', $template->exists ? 'Edit Template Email' : 'Tambah Template Email')

@section('content')
<div class="max-w-2xl">
    <form action="{{ $template->exists ? route('super-admin.email-templates.update', $template->id) : route('super-admin.email-templates.store') }}" method="POST" class="space-y-4">
        @csrf
        @if($template->exists) @method('PUT') @endif

        <div class="card p-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nama Template <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" class="form-input" required>
                </div>
                @if(!$template->exists)
                <div>
                    <label class="form-label">Slug (ID unik) <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="form-input font-mono" required placeholder="welcome_user">
                </div>
                @endif
            </div>

            <div>
                <label class="form-label">Subject Email <span class="text-red-500">*</span></label>
                <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Isi Email (HTML) <span class="text-red-500">*</span></label>
                <textarea name="body" class="form-input font-mono text-sm" rows="12" required>{{ old('body', $template->body ?? '') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Mendukung HTML. Gunakan placeholder seperti: {{ '{' }}{{ '{' }}name{{ '}' }}{{ '}' }}, {{ '{' }}{{ '{' }}amount{{ '}' }}{{ '}' }}</p>
            </div>

            <div>
                <label class="form-label">Placeholder (pisah koma)</label>
                <input type="text" name="placeholders" value="{{ old('placeholders', $template->exists && is_array($template->placeholders) ? implode(', ', $template->placeholders) : '') }}" class="form-input" placeholder="name, email, amount">
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded text-emerald-600" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm">Aktif</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary px-6">Simpan</button>
            <a href="{{ route('super-admin.email-templates.index') }}" class="btn-secondary px-6">Batal</a>
        </div>
    </form>
</div>
@endsection
