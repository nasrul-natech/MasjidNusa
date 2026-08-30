<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\BrandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandingController extends Controller
{
    private const BRANDING_FIELDS = [
        'app_name'       => ['group' => 'branding', 'label' => 'Nama Aplikasi'],
        'app_tagline'    => ['group' => 'branding', 'label' => 'Tagline'],
        'mosque_name'    => ['group' => 'general', 'label' => 'Nama Masjid'],
        'primary_color'  => ['group' => 'branding', 'label' => 'Warna Utama'],
        'accent_color'   => ['group' => 'branding', 'label' => 'Warna Aksen'],
    ];

    private const FILE_FIELDS = [
        'logo'       => 'logo_path',
        'logo_icon'  => 'logo_icon_path',
        'favicon'    => 'favicon_path',
    ];

    public function index()
    {
        $settings = Setting::whereIn('group', ['branding', 'general'])
            ->whereIn('key', array_merge(array_keys(self::BRANDING_FIELDS), array_values(self::FILE_FIELDS)))
            ->pluck('value', 'key')
            ->toArray();

        return view('super-admin.branding.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name'      => ['required', 'string', 'max:100'],
            'app_tagline'   => ['nullable', 'string', 'max:200'],
            'mosque_name'   => ['required', 'string', 'max:100'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color'  => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo'          => ['nullable', 'image', 'max:2048'],
            'logo_icon'     => ['nullable', 'image', 'max:2048'],
            'favicon'       => ['nullable', 'image', 'max:1024'],
        ]);

        foreach (self::BRANDING_FIELDS as $key => $meta) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $validated[$key],
                    'group' => $meta['group'],
                    'label' => $meta['label'],
                ]
            );
        }

        foreach (self::FILE_FIELDS as $input => $settingKey) {
            if (!$request->hasFile($input)) {
                continue;
            }

            $oldPath = Setting::where('key', $settingKey)->value('value');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file($input)->store('branding', 'public');

            Setting::updateOrCreate(
                ['key' => $settingKey],
                [
                    'value' => $path,
                    'group' => 'branding',
                    'label' => ucfirst(str_replace('_', ' ', $settingKey)),
                ]
            );
        }

        BrandingService::clearCache();

        return back()->with('success', 'Branding berhasil diperbarui.');
    }
}
