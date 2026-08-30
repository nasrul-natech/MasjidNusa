<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PrayerLocation;
use App\Models\PrayerSchedule;
use App\Models\PrayerTime;
use App\Services\PrayerLocationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrayerScheduleController extends Controller
{
    /**
     * Resolve prayer location from GPS — administrative area first, distance as tiebreaker.
     */
    public function nearestJson(Request $request, PrayerLocationResolver $resolver): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = (float) $request->lat;
        $lon = (float) $request->lon;

        $nearest = $resolver->resolve($lat, $lon);

        if (!$nearest) {
            return response()->json(['location_id' => null, 'location_name' => null]);
        }

        $distanceKm = $resolver->haversineKm(
            $lat,
            $lon,
            (float) $nearest->latitude,
            (float) $nearest->longitude
        );

        return response()->json([
            'location_id'   => $nearest->id,
            'city_name'     => $nearest->city_name,
            'province_name' => $nearest->province_name,
            'location_name' => $nearest->full_name,
            'distance_km'   => round($distanceKm, 1),
        ]);
    }

    public function todayJson(Request $request): JsonResponse
    {
        $location = $request->filled('location_id')
            ? PrayerLocation::find($request->location_id)
            : PrayerLocation::getDefault();

        if (!$location) {
            return response()->json(['schedule' => null, 'location_name' => null]);
        }

        $today = PrayerTime::where('location_id', $location->id)
            ->where('date', now()->toDateString())
            ->first();

        return response()->json([
            'location_name' => $location->city_name,
            'schedule' => $today ? [
                ['name' => 'Subuh',   'time' => substr($today->subuh,   0, 5)],
                ['name' => 'Dzuhur',  'time' => substr($today->dzuhur,  0, 5)],
                ['name' => 'Ashar',   'time' => substr($today->ashar,   0, 5)],
                ['name' => 'Maghrib', 'time' => substr($today->maghrib, 0, 5)],
                ['name' => 'Isya',    'time' => substr($today->isya,    0, 5)],
            ] : null,
        ]);
    }

    public function index(Request $request)
    {
        $locations = PrayerLocation::orderBy('province_name')->orderBy('city_name')->get();
        $selectedLocationId = $request->get('location_id');

        $selectedLocation = null;
        $todaySchedule = null;
        $useFallback = false;

        if ($selectedLocationId) {
            $selectedLocation = PrayerLocation::find($selectedLocationId);
        } elseif ($locations->count()) {
            $selectedLocation = PrayerLocation::getDefault();
        }

        if ($selectedLocation) {
            $todaySchedule = PrayerTime::where('location_id', $selectedLocation->id)
                ->where('date', now()->toDateString())
                ->first();
        }

        if (!$todaySchedule) {
            $useFallback = true;
        }

        $dailySchedules = PrayerSchedule::where('type', 'daily')
            ->where('is_active', true)
            ->orderBy('time')
            ->get();
        $jumatSchedule = PrayerSchedule::where('type', 'jumat')
            ->where('is_active', true)
            ->first();

        return view('public.jadwal-sholat', compact(
            'locations', 'selectedLocation', 'todaySchedule',
            'dailySchedules', 'jumatSchedule', 'useFallback'
        ));
    }
}
