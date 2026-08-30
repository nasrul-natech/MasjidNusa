<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\ManagementHistory;
use Illuminate\Http\Request;

class ManagementHistoryController extends Controller
{
    public function index()
    {
        $managements = ManagementHistory::orderByDesc('start_year')->paginate(15);
        return view('admin.operasional.management-history.index', compact('managements'));
    }

    public function create()
    {
        $management = null;
        return view('admin.operasional.management-history.form', compact('management'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'end_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'is_current' => ['boolean'],
        ]);

        if ($data['is_current'] ?? false) {
            ManagementHistory::where('is_current', true)->update(['is_current' => false]);
        }

        ManagementHistory::create($data);
        return redirect()->route('admin.operasional.management-history.index')->with('success', 'Kepengurusan berhasil ditambahkan.');
    }

    public function edit(ManagementHistory $managementHistory)
    {
        return view('admin.operasional.management-history.form', ['management' => $managementHistory]);
    }

    public function update(Request $request, ManagementHistory $managementHistory)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_year' => ['required', 'integer'],
            'end_year' => ['nullable', 'integer'],
            'is_current' => ['boolean'],
        ]);

        if ($data['is_current'] ?? false) {
            ManagementHistory::where('id', '!=', $managementHistory->id)->update(['is_current' => false]);
        }

        $managementHistory->update($data);
        return redirect()->route('admin.operasional.management-history.index')->with('success', 'Kepengurusan berhasil diperbarui.');
    }

    public function destroy(ManagementHistory $managementHistory)
    {
        $managementHistory->delete();
        return back()->with('success', 'Kepengurusan berhasil dihapus.');
    }
}
