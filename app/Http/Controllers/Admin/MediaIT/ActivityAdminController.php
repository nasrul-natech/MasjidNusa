<?php

namespace App\Http\Controllers\Admin\MediaIT;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityAdminController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'upcoming');
        $query = Activity::with('category')->orderByDesc('created_at');

        if ($filter === 'past') {
            $query->where('start_date', '<', now());
        } else {
            $query->where('start_date', '>=', now())->orderBy('start_date');
        }

        $activities = $query->paginate(15);
        $categories = ActivityCategory::all();

        return view('admin.media-it.activities.index', compact('activities', 'categories', 'filter'));
    }

    public function create()
    {
        $categories = ActivityCategory::all();
        $activity = null;
        return view('admin.media-it.activities.form', compact('activity', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:activity_categories,id'],
            'location' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,publish,selesai,arsip'],
            'is_featured' => ['boolean'],
            'show_popup' => ['boolean'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . time();
        $data['created_by'] = auth()->id();

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('activities', 'public');
        }

        $activity = Activity::create($data);

        // Send notifications if published
        if ($data['status'] === 'publish') {
            $this->notifyNewActivity($activity);
        }

        return redirect()->route('admin.media-it.activities.index')->with('success', 'Kegiatan berhasil dibuat.');
    }

    public function edit(Activity $activity)
    {
        $categories = ActivityCategory::all();
        return view('admin.media-it.activities.form', compact('activity', 'categories'));
    }

    public function update(Request $request, Activity $activity)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:activity_categories,id'],
            'location' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,publish,selesai,arsip'],
            'is_featured' => ['boolean'],
            'show_popup' => ['boolean'],
            'banner' => ['nullable', 'image', 'max:4096'],
        ]);

        $wasPublished = $activity->status !== 'publish';
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('activities', 'public');
        }

        $activity->update($data);

        if ($wasPublished && $data['status'] === 'publish') {
            $this->notifyNewActivity($activity->fresh());
        }

        return redirect()->route('admin.media-it.activities.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        return back()->with('success', 'Kegiatan dihapus.');
    }

    public function publish(int $id)
    {
        $activity = Activity::findOrFail($id);
        $activity->update(['status' => 'publish']);
        $this->notifyNewActivity($activity);
        return back()->with('success', 'Kegiatan dipublikasikan.');
    }

    private function notifyNewActivity(Activity $activity): void
    {
        $this->notificationService->notifyAllUsers(
            '📅 Kegiatan Baru: ' . $activity->title,
            'Ada kegiatan baru pada ' . $activity->start_date->format('d F Y'),
            'activity',
            route('kegiatan.show', $activity->slug)
        );
    }
}
