<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\MosqueHistory;
use App\Models\MosqueProfile;
use Illuminate\Http\Request;

class MosqueHistoryController extends Controller
{
    public function index()
    {
        $histories = MosqueHistory::orderBy('event_date')->paginate(15);
        return view('admin.operasional.mosque-history.index', compact('histories'));
    }

    public function create()
    {
        $mosque = MosqueProfile::first();
        $history = null;
        return view('admin.operasional.mosque-history.form', compact('mosque', 'history'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'event_date' => ['nullable', 'date'],
            'sort_order' => ['integer'],
        ]);

        $mosque = MosqueProfile::firstOrCreate(['name' => 'Masjid Nusa']);
        $data['mosque_profile_id'] = $mosque->id;

        MosqueHistory::create($data);
        return redirect()->route('admin.operasional.mosque-history.index')->with('success', 'Sejarah berhasil ditambahkan.');
    }

    public function edit(MosqueHistory $mosqueHistory)
    {
        return view('admin.operasional.mosque-history.form', ['history' => $mosqueHistory]);
    }

    public function update(Request $request, MosqueHistory $mosqueHistory)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'event_date' => ['nullable', 'date'],
            'sort_order' => ['integer'],
        ]);
        $mosqueHistory->update($data);
        return redirect()->route('admin.operasional.mosque-history.index')->with('success', 'Sejarah berhasil diperbarui.');
    }

    public function destroy(MosqueHistory $mosqueHistory)
    {
        $mosqueHistory->delete();
        return back()->with('success', 'Sejarah berhasil dihapus.');
    }
}
