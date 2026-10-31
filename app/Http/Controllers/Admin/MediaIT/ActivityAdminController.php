<?php

namespace App\Http\Controllers\Admin\MediaIT;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityAttendanceSetting;
use App\Models\ActivityCategory;
use App\Models\AuditLog;
use App\Services\NotificationService;
use App\Support\NewFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ActivityAdminController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'upcoming');
        $query = Activity::with('category')->orderByDesc('created_at');

        if ($filter === 'past') {
            $query->where('start_date', '<', now());
            if (NewFeature::enabled('activity_attendance')) {
                $query->whereNotIn('id', Activity::currentAgenda()->select('id'));
            }
        } else {
            if (NewFeature::enabled('activity_attendance')) {
                $query->currentAgenda()->reorder()->orderBy('start_date');
            } else {
                $query->where('start_date', '>=', now())->orderBy('start_date');
            }
        }

        if (NewFeature::enabled('activity_attendance')) {
            $query->with(['attendanceSetting', 'attendanceSession']);
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

    public function complete(Request $request, Activity $activity, \App\Services\ActivityAttendanceService $service)
    {
        $service->open($activity, $request->user());

        return back()->with('success', 'Kegiatan diselesaikan. Check-in dibuka sesuai durasi kegiatan; batas waktu tidak berubah saat tombol ditekan ulang.');
    }

    public function store(Request $request)
    {
        $attendanceData = $this->attendanceData($request);
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

        if ($data['status'] === 'publish' && !auth()->user()->can('publish_content')) {
            return back()->with('error', 'Anda tidak memiliki izin menerbitkan konten.')->withInput();
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('activities', 'public');
        }

        $activity = DB::transaction(function () use ($data, $attendanceData) {
            $activity = Activity::create($data);
            $this->saveAttendance($activity, $attendanceData);

            return $activity;
        });

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
        $attendanceData = $this->attendanceData($request);
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
        if ($data['status'] === 'publish' && $wasPublished && !auth()->user()->can('publish_content')) {
            return back()->with('error', 'Anda tidak memiliki izin menerbitkan konten.')->withInput();
        }

        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('activities', 'public');
        }

        DB::transaction(function () use ($activity, $data, $attendanceData) {
            $locked = Activity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            $session = $locked->attendanceSession;
            if (NewFeature::enabled('activity_attendance') && $session && now()->lt($session->closes_at) && $data['status'] !== 'publish') {
                throw ValidationException::withMessages(['status' => 'Tunggu check-in ditutup sebelum mengubah status publikasi.']);
            }
            if (NewFeature::enabled('activity_attendance') && $session && ! $locked->start_date->equalTo(\Carbon\Carbon::parse($data['start_date']))) {
                throw ValidationException::withMessages(['start_date' => 'Waktu mulai terkunci setelah sesi absensi dibuka.']);
            }
            $this->saveAttendance($locked, $attendanceData);
            $locked->update($data);
        });

        if ($wasPublished && $data['status'] === 'publish') {
            $this->notifyNewActivity($activity->fresh());
        }

        return redirect()->route('admin.media-it.activities.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Activity $activity)
    {
        $deleted = DB::transaction(function () use ($activity) {
            $locked = Activity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            if ($locked->attendanceSession()->exists()) {
                return false;
            }
            $locked->delete();

            return true;
        });
        if (! $deleted) {
            return back()->with('error', 'Kegiatan memiliki sesi absensi. Gunakan arsip setelah check-in ditutup, bukan hapus.');
        }
        return back()->with('success', 'Kegiatan dihapus.');
    }

    public function publish(int $id)
    {
        if (!auth()->user()->can('publish_content')) {
            return back()->with('error', 'Anda tidak memiliki izin menerbitkan konten.');
        }

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

    private function attendanceData(Request $request): ?array
    {
        if (! NewFeature::enabled('activity_attendance') || ! $request->user()->can('configure_activity_attendance') || ! $request->has('attendance_enabled')) {
            return null;
        }

        return $request->validate([
            'attendance_enabled' => ['required', 'boolean'],
            'attendance_minutes' => ['required', 'integer', 'min:1', 'max:30'],
        ]);
    }

    private function saveAttendance(Activity $activity, ?array $data): void
    {
        if ($data === null) {
            return;
        }
        $values = ['enabled' => (bool) $data['attendance_enabled'], 'duration_seconds' => (int) $data['attendance_minutes'] * 60];
        $existing = $activity->attendanceSetting;
        if ($activity->attendanceSession()->exists()) {
            if (! $existing || $existing->enabled !== $values['enabled'] || $existing->duration_seconds !== $values['duration_seconds']) {
                throw ValidationException::withMessages(['attendance_minutes' => 'Pengaturan absensi terkunci setelah sesi dibuka.']);
            }

            return;
        }
        $old = $existing?->only(['enabled', 'duration_seconds']) ?? [];
        ActivityAttendanceSetting::updateOrCreate(['activity_id' => $activity->id], $values);
        if ($old !== $values) {
            AuditLog::record('activity.attendance_configured', $activity, $old, $values);
        }
    }
}
