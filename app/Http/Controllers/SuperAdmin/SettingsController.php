<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $excludeKeys = [
            'app_name', 'app_tagline', 'mosque_name',
            'primary_color', 'accent_color',
            'logo_path', 'logo_icon_path', 'favicon_path',
        ];

        $settings = Setting::whereNotIn('key', $excludeKeys)
            ->orderBy('group')
            ->get()
            ->groupBy('group');

        return view('super-admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = $request->except(['_token', '_method']);

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
