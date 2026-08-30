<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerSchedule extends Model
{
    protected $fillable = [
        'prayer_name', 'time', 'date', 'type', 'notes', 'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public static function getTodaySchedule(): array
    {
        $defaultLocation = PrayerLocation::getDefault();

        if ($defaultLocation) {
            $imported = PrayerTime::where('location_id', $defaultLocation->id)
                ->where('date', now()->toDateString())
                ->first();

            if ($imported) {
                return $imported->toScheduleArray();
            }
        }

        return self::where('type', 'daily')
            ->where('is_active', true)
            ->orderBy('time')
            ->get()
            ->toArray();
    }

    public static function getOrderedPrayers(): array
    {
        return ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya', "Jum'at"];
    }
}
