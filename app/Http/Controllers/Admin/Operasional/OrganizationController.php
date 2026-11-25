<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\ManagementHistory;
use App\Models\OrganizationStructure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

        $this->validateHierarchy($data);

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

        $this->validateHierarchy($data, $organization);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('org-structure', 'public');
        }

        $organization->update($data);

        return redirect()->route('admin.operasional.organization.index')->with('success', 'Struktur berhasil diperbarui.');
    }

    private function validateHierarchy(array $data, ?OrganizationStructure $organization = null): void
    {
        $managementId = (int) $data['management_history_id'];
        $parentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $organization?->parent_id;
        $visited = $organization ? [$organization->id => true] : [];

        while ($parentId !== null) {
            if (isset($visited[$parentId])) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Induk struktur tidak boleh membentuk siklus.',
                ]);
            }

            $visited[$parentId] = true;
            $parent = OrganizationStructure::find($parentId);

            if (! $parent || (int) $parent->management_history_id !== $managementId) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Induk struktur harus berada pada periode kepengurusan yang sama.',
                ]);
            }

            $parentId = $parent->parent_id;
        }

        if ($organization && $organization->children()->where('management_history_id', '!=', $managementId)->exists()) {
            throw ValidationException::withMessages([
                'management_history_id' => 'Periode kepengurusan harus sama dengan anggota anak struktur.',
            ]);
        }
    }

    public function destroy(OrganizationStructure $organization)
    {
        $organization->delete();

        return back()->with('success', 'Anggota dihapus.');
    }
}
