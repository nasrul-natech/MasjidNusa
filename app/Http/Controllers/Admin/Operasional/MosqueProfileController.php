<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MosqueProfile;
use Illuminate\Http\Request;

class MosqueProfileController extends Controller
{
    public function index()
    {
        $mosque = MosqueProfile::first();
        return view('admin.operasional.mosque-profile.index', compact('mosque'));
    }

    public function edit(MosqueProfile $mosqueProfile)
    {
        return view('admin.operasional.mosque-profile.edit', ['mosque' => $mosqueProfile]);
    }

    public function update(Request $request, MosqueProfile $mosqueProfile)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'description' => ['nullable', 'string'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'google_maps_url' => ['nullable', 'url'],
            'website' => ['nullable', 'url'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('mosque', 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('mosque', 'public');
        }

        $mosqueProfile->update($data);
        AuditLog::record('mosque_profile.updated', $mosqueProfile);

        return back()->with('success', 'Profil masjid berhasil diperbarui.');
    }
}
