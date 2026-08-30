<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\ManagementHistory;
use App\Models\OrganizationStructure;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $managements = ManagementHistory::orderByDesc('start_year')->with('allStructures')->get();
        return view('admin.operasional.organization.index', compact('managements'));
    }

    public function create()
    {
        $managements = ManagementHistory::orderByDesc('start_year')->get();
        $organization = null;
        return view('admin.operasional.organization.form', compact('organization', 'managements'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'management_history_id' => ['required', 'exists:management_histories,id'],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:organization_structures,id'],
            'sort_order' => ['integer'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('org-structure', 'public');
        }

        OrganizationStructure::create($data);
        return redirect()->route('admin.operasional.organization.index')->with('success', 'Anggota struktur berhasil ditambahkan.');
    }

    public function edit(OrganizationStructure $organization)
    {
        $managements = ManagementHistory::orderByDesc('start_year')->get();
        return view('admin.operasional.organization.form', compact('organization', 'managements'));
    }

    public function update(Request $request, OrganizationStructure $organization)
    {
        $data = $request->validate([
            'management_history_id' => ['required', 'exists:management_histories,id'],
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:organization_structures,id'],
            'sort_order' => ['integer'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('org-structure', 'public');
        }

        $organization->update($data);
        return redirect()->route('admin.operasional.organization.index')->with('success', 'Struktur berhasil diperbarui.');
    }

    public function destroy(OrganizationStructure $organization)
    {
        $organization->delete();
        return back()->with('success', 'Anggota dihapus.');
    }
}
