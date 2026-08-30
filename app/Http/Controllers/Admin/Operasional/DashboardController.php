<?php

namespace App\Http\Controllers\Admin\Operasional;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\MosqueProfile;
use App\Models\PrayerSchedule;

class DashboardController extends Controller
{
    public function index()
    {
        $mosque = MosqueProfile::first();
        $prayerCount = PrayerSchedule::where('is_active', true)->count();
        $activityCount = Activity::where('status', 'publish')->count();
        $upcomingCount = Activity::published()->upcoming()->count();

        return view('admin.operasional.dashboard', compact(
            'mosque', 'prayerCount', 'activityCount', 'upcomingCount'
        ));
    }
}
