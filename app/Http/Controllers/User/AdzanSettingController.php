<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PrayerLocation;
use App\Models\PrayerTime;
use App\Models\UserAdzanSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdzanSettingController extends Controller
{
    public function index()
    {
        $settings = UserAdzanSetting::getOrCreateForUser(auth()->id());
        $locations = PrayerLocation::orderBy('province_name')->orderBy('city_name')->get();

        $activeLocation = $settings->location_id
            ? $locations->firstWhere('id', $settings->location_id)
            : null;

        $todaySchedule = null;
        if ($activeLocation) {
            $todaySchedule = PrayerTime::where('location_id', $activeLocation->id)
                ->where('date', now()->toDateString())
                ->first();
        }

        return view('user.adzan-settings', compact('settings', 'locations', 'todaySchedule', 'activeLocation'));
    }

    public function syncLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'location_id' => ['required', 'exists:prayer_locations,id'],
        ]);

        $settings = UserAdzanSetting::getOrCreateForUser(auth()->id());
        $settings->update(['location_id' => $data['location_id']]);

        return response()->json(['ok' => true, 'location_id' => $settings->location_id]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'location_id'     => ['nullable', 'exists:prayer_locations,id'],
            'imsak_enabled'   => ['boolean'],
            'subuh_enabled'   => ['boolean'],
            'dzuhur_enabled'  => ['boolean'],
            'ashar_enabled'   => ['boolean'],
            'maghrib_enabled' => ['boolean'],
            'isya_enabled'    => ['boolean'],
            'sound_enabled'   => ['boolean'],
            'sound_type'      => ['string', 'in:simple,makkah,madinah'],
            'volume'          => ['integer', 'between:0,100'],
            'push_enabled'    => ['boolean'],
            'minutes_before'  => ['integer', 'between:0,30'],
        ]);

        foreach (['imsak_enabled', 'subuh_enabled', 'dzuhur_enabled', 'ashar_enabled', 'maghrib_enabled', 'isya_enabled', 'sound_enabled', 'push_enabled'] as $bool) {
            $data[$bool] = $request->boolean($bool);
        }

        $settings = UserAdzanSetting::getOrCreateForUser(auth()->id());
        $settings->update($data);

        return back()->with('success', 'Pengaturan adzan berhasil disimpan.');
    }

    /**
     * JSON endpoint: today's schedule for adzan client timer.
     * Works for both auth and guest (uses location_id param or localStorage preference).
     */
    public function todayScheduleJson(Request $request): JsonResponse
    {
        $locationId = $request->input('location_id');

        if (!$locationId && auth()->check()) {
            $settings = auth()->user()->adzanSetting;
            $locationId = $settings?->location_id;
        }

        if (!$locationId) {
            $locationId = PrayerLocation::getDefault()?->id;
        }

        if (!$locationId) {
            return response()->json(['schedule' => null]);
        }

        $pt = PrayerTime::where('location_id', $locationId)
            ->where('date', now()->toDateString())
            ->first();

        if (!$pt) {
            return response()->json(['schedule' => null]);
        }

        return response()->json([
            'schedule' => [
                'imsak'   => substr($pt->imsak, 0, 5),
                'subuh'   => substr($pt->subuh, 0, 5),
                'terbit'  => substr($pt->terbit, 0, 5),
                'dhuha'   => substr($pt->dhuha, 0, 5),
                'dzuhur'  => substr($pt->dzuhur, 0, 5),
                'ashar'   => substr($pt->ashar, 0, 5),
                'maghrib' => substr($pt->maghrib, 0, 5),
                'isya'    => substr($pt->isya, 0, 5),
            ],
            'location_id' => $locationId,
        ]);
    }
}
