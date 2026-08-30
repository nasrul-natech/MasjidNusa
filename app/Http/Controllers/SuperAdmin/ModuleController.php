<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = SystemModule::all();
        return view('super-admin.modules.index', compact('modules'));
    }

    public function update(Request $request, SystemModule $module)
    {
        $data = $request->validate(['is_active' => ['boolean']]);
        $module->update($data);
        Cache::forget("module_{$module->slug}");
        return back()->with('success', 'Modul diperbarui.');
    }

    public function toggle(int $id)
    {
        $module = SystemModule::findOrFail($id);
        if ($module->is_core) {
            return back()->with('error', 'Modul inti tidak bisa dinonaktifkan.');
        }
        $module->update(['is_active' => !$module->is_active]);
        Cache::forget("module_{$module->slug}");
        return back()->with('success', 'Modul ' . ($module->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.');
    }
}
