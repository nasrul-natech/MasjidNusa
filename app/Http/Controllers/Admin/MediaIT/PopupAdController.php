<?php

namespace App\Http\Controllers\Admin\MediaIT;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\PopupAd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PopupAdController extends Controller
{
    public function index(Request $request)
    {
        $query = PopupAd::with('activity')->orderBy('priority')->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->where('ad_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $popups = $query->paginate(15)->withQueryString();
        $counts = [
            'total' => PopupAd::count(),
            'active' => PopupAd::where('is_active', true)->count(),
            'image' => PopupAd::where('ad_type', 'image')->count(),
            'link' => PopupAd::where('ad_type', 'link')->count(),
        ];

        return view('admin.media-it.popup-ads.index', compact('popups', 'counts'));
    }

    public function create(Request $request)
    {
        $activities = Activity::published()->orderBy('start_date')->get();
        $popupAd = null;
        $adType = $request->get('type', 'image');

        return view('admin.media-it.popup-ads.form', compact('popupAd', 'activities', 'adType'));
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'ad_type' => ['required', 'in:image,link'],
            'is_active' => ['boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'duration' => ['required', 'integer', 'min:3', 'max:60'],
            'priority' => ['integer', 'min:0'],
            'image' => ['required', 'image', 'max:4096'],
        ];

        if ($request->ad_type === 'link') {
            $rules['link'] = ['required', 'string', 'max:255'];
            $rules['content'] = ['nullable', 'string', 'max:500'];
            $rules['activity_id'] = ['nullable', 'exists:activities,id'];
        }

        $data = $request->validate($rules);

        $data['image'] = $request->file('image')->store('popup-ads', 'public');
        $data['is_active'] = $request->boolean('is_active');

        PopupAd::create($data);

        return redirect()->route('admin.media-it.popup-ads.index')
            ->with('success', 'Popup iklan berhasil dibuat.');
    }

    public function edit(PopupAd $popupAd)
    {
        $activities = Activity::published()->orderBy('start_date')->get();
        $adType = $popupAd->ad_type;

        return view('admin.media-it.popup-ads.form', compact('popupAd', 'activities', 'adType'));
    }

    public function update(Request $request, PopupAd $popupAd)
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'ad_type' => ['required', 'in:image,link'],
            'is_active' => ['boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'duration' => ['required', 'integer', 'min:3', 'max:60'],
            'priority' => ['integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];

        if ($request->ad_type === 'link') {
            $rules['link'] = ['required', 'string', 'max:255'];
            $rules['content'] = ['nullable', 'string', 'max:500'];
            $rules['activity_id'] = ['nullable', 'exists:activities,id'];
        }

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->ad_type !== 'link') {
            $data['link'] = null;
            $data['content'] = null;
            $data['activity_id'] = null;
        }

        if ($request->hasFile('image')) {
            if ($popupAd->image) {
                Storage::disk('public')->delete($popupAd->image);
            }
            $data['image'] = $request->file('image')->store('popup-ads', 'public');
        }

        $popupAd->update($data);

        return redirect()->route('admin.media-it.popup-ads.index')
            ->with('success', 'Popup iklan berhasil diperbarui.');
    }

    public function destroy(PopupAd $popupAd)
    {
        if ($popupAd->image) {
            Storage::disk('public')->delete($popupAd->image);
        }
        $popupAd->delete();

        return back()->with('success', 'Popup iklan berhasil dihapus.');
    }

    public function toggleActive(PopupAd $popupAd)
    {
        $popupAd->update(['is_active' => ! $popupAd->is_active]);
        $status = $popupAd->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Popup berhasil {$status}.");
    }
}
