<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\DonationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DonationCategoryController extends Controller
{
    public function index()
    {
        $categories = DonationCategory::withCount('campaigns')->orderBy('name')->get();
        return view('admin.keuangan.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . time();

        DonationCategory::create($data);

        return back()->with('success', "Kategori \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, DonationCategory $donationCategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $donationCategory->update($data);

        return back()->with('success', "Kategori \"{$data['name']}\" berhasil diperbarui.");
    }

    public function destroy(DonationCategory $donationCategory)
    {
        if ($donationCategory->campaigns()->count()) {
            return back()->with('error', "Kategori tidak bisa dihapus karena masih digunakan oleh {$donationCategory->campaigns()->count()} program donasi.");
        }

        $name = $donationCategory->name;
        $donationCategory->delete();

        return back()->with('success', "Kategori \"{$name}\" berhasil dihapus.");
    }
}
