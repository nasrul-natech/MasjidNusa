<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Article;
use App\Models\Banner;
use App\Models\DonationCampaign;
use App\Models\MosqueProfile;
use App\Models\PrayerLocation;
use App\Models\PrayerSchedule;
use App\Models\PrayerTime;

class HomeController extends Controller
{
    public function index()
    {
        $allBanners = Banner::active()->get();
        $desktopBanners = $allBanners->filter(fn($b) => in_array($b->display_type, ['all', 'desktop']));
        $mobileBanners = $allBanners->filter(fn($b) => in_array($b->display_type, ['all', 'mobile']));

        $mosque = MosqueProfile::current();

        // Location-based prayer times (API data)
        $locations       = PrayerLocation::orderBy('province_name')->orderBy('city_name')->get();
        $defaultLocation = PrayerLocation::getDefault();
        $todayTimes      = $defaultLocation
            ? PrayerTime::where('location_id', $defaultLocation->id)
                        ->where('date', now()->toDateString())
                        ->first()
            : null;

        // Fallback: manual prayer schedule (used if no PrayerTime data exists)
        $prayerSchedules = PrayerSchedule::where('type', 'daily')
            ->where('is_active', true)
            ->orderBy('time')
            ->get();

        if (\App\Support\NewFeature::enabled('activity_attendance')) {
            $upcomingActivities = Activity::published()->with(['attendanceSetting', 'attendanceSession'])
                ->currentAgenda()->orderByRaw('CASE WHEN start_date <= ? THEN 0 ELSE 1 END', [now()])
                ->orderBy('start_date')->limit(4)->get();
        } else {
            $upcomingActivities = Activity::published()->upcoming()->limit(4)->get();
        }
        $latestArticles = Article::published()->orderByDesc('published_at')->limit(4)->get();
        $activeCampaigns = DonationCampaign::active()->where('is_featured', true)->limit(3)->get();

        return view('public.home', compact(
            'desktopBanners', 'mobileBanners', 'mosque',
            'locations', 'defaultLocation', 'todayTimes', 'prayerSchedules',
            'upcomingActivities', 'latestArticles', 'activeCampaigns'
        ));
    }
}
