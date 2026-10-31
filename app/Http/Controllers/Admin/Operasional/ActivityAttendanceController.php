<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Services\ActivityAttendanceService;
use Illuminate\Http\Request;

class ActivityAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['search' => ['nullable', 'string', 'max:150']]);
        $activities = Activity::with(['attendanceSetting', 'attendanceSession' => fn ($query) => $query->withCount('attendances')])
            ->whereHas('attendanceSetting', fn ($query) => $query->where('enabled', true))
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search').'%'))
            ->orderByDesc('start_date')->paginate(20)->withQueryString();

        return view('admin.operasional.attendance.index', compact('activities'));
    }

    public function open(Activity $activity, Request $request, ActivityAttendanceService $service)
    {
        $service->open($activity, $request->user());

        return back()->with('success', 'Kegiatan selesai secara operasional. Jendela check-in dibuka tanpa mengubah status publikasi.');
    }
}
