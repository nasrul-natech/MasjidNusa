<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::all();
        return view('super-admin.email-templates.index', compact('templates'));
    }

    public function create()
    {
        $template = null;
        return view('super-admin.email-templates.form', compact('template'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'slug' => ['required', 'string', 'unique:email_templates,slug'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
            'placeholders' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        if ($data['placeholders']) {
            $data['placeholders'] = array_map('trim', explode(',', $data['placeholders']));
        }

        EmailTemplate::create($data);
        return redirect()->route('super-admin.email-templates.index')->with('success', 'Template berhasil dibuat.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('super-admin.email-templates.form', ['template' => $emailTemplate]);
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
            'placeholders' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        if (isset($data['placeholders'])) {
            $data['placeholders'] = array_map('trim', explode(',', $data['placeholders']));
        }

        $emailTemplate->update($data);
        return redirect()->route('super-admin.email-templates.index')->with('success', 'Template diperbarui.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return back()->with('success', 'Template dihapus.');
    }
}
