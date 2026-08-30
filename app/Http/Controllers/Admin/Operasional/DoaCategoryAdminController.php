<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\DoaCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoaCategoryAdminController extends Controller
{
    public function index()
    {
        $categories = DoaCategory::withCount('duas')->orderBy('sort_order')->get();
        return view('admin.operasional.doa-category.index', compact('categories'));
    }

    public function create()
    {
        $category = null;
        return view('admin.operasional.doa-category.form', compact('category'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'icon'       => ['nullable', 'string', 'max:50'],
            'sort_order' => ['integer'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['icon'] = $data['icon'] ?: 'book';

        DoaCategory::create($data);

        return redirect()->route('admin.operasional.doa-category.index')
            ->with('success', 'Kategori doa berhasil ditambahkan.');
    }

    public function edit(DoaCategory $doaCategory)
    {
        return view('admin.operasional.doa-category.form', ['category' => $doaCategory]);
    }

    public function update(Request $request, DoaCategory $doaCategory)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'icon'       => ['nullable', 'string', 'max:50'],
            'sort_order' => ['integer'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['icon'] = $data['icon'] ?: 'book';

        $doaCategory->update($data);

        return redirect()->route('admin.operasional.doa-category.index')
            ->with('success', 'Kategori doa berhasil diperbarui.');
    }

    public function destroy(DoaCategory $doaCategory)
    {
        $doaCategory->delete();
        return back()->with('success', 'Kategori doa berhasil dihapus.');
    }
}
