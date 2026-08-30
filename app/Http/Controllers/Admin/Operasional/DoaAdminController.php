<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\Doa;
use App\Models\DoaCategory;
use App\Support\TableSort;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoaAdminController extends Controller
{
    public function index(Request $request)
    {
        $categories = DoaCategory::orderBy('sort_order')->get();

        $query = Doa::with('category');

        if ($request->filled('category')) {
            $query->where('doa_category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title', 'like', "%{$s}%")
                ->orWhere('translation', 'like', "%{$s}%"));
        }

        TableSort::apply($query, ['title', 'sort_order', 'status', 'created_at'], 'sort_order', 'asc');

        $duas = $query->paginate(20)
            ->appends($request->query());

        return view('admin.operasional.doa.index', compact('duas', 'categories'));
    }

    public function create()
    {
        $doa = null;
        $categories = DoaCategory::orderBy('sort_order')->get();
        return view('admin.operasional.doa.form', compact('doa', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateDoa($request);
        $data['slug'] = Str::slug($data['title']);

        $existing = Doa::where('slug', $data['slug'])->exists();
        if ($existing) {
            $data['slug'] .= '-' . Str::random(4);
        }

        Doa::create($data);

        return redirect()->route('admin.operasional.doa.index')
            ->with('success', 'Doa berhasil ditambahkan.');
    }

    public function edit(Doa $doa)
    {
        $categories = DoaCategory::orderBy('sort_order')->get();
        return view('admin.operasional.doa.form', compact('doa', 'categories'));
    }

    public function update(Request $request, Doa $doa)
    {
        $data = $this->validateDoa($request);
        $newSlug = Str::slug($data['title']);

        if ($newSlug !== $doa->slug) {
            $existing = Doa::where('slug', $newSlug)->where('id', '!=', $doa->id)->exists();
            $data['slug'] = $existing ? $newSlug . '-' . Str::random(4) : $newSlug;
        }

        $doa->update($data);

        return redirect()->route('admin.operasional.doa.index')
            ->with('success', 'Doa berhasil diperbarui.');
    }

    public function destroy(Doa $doa)
    {
        $doa->delete();
        return back()->with('success', 'Doa berhasil dihapus.');
    }

    private function validateDoa(Request $request): array
    {
        return $request->validate([
            'doa_category_id' => ['required', 'exists:doa_categories,id'],
            'title'           => ['required', 'string', 'max:255'],
            'arabic'          => ['nullable', 'string'],
            'latin'           => ['nullable', 'string'],
            'translation'     => ['required', 'string'],
            'content_html'    => ['nullable', 'string'],
            'content_text'    => ['nullable', 'string'],
            'source'          => ['nullable', 'string', 'max:255'],
            'status'          => ['required', 'in:draft,publish'],
            'sort_order'      => ['integer'],
        ]);
    }
}
