<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerTime extends Model
{
    protected $fillable = [
        'location_id', 'date', 'imsak', 'subuh', 'terbit', 'dhuha',
        'dzuhur', 'ashar', 'maghrib', 'isya',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function location()
    {
        return $this->belongsTo(PrayerLocation::class, 'location_id');
    }

    public function toScheduleArray(): array
    {
        return [
            ['name' => 'Imsak', 'time' => $this->imsak, 'icon' => 'moon'],
            ['name' => 'Subuh', 'time' => $this->subuh, 'icon' => 'sunrise'],
            ['name' => 'Terbit', 'time' => $this->terbit, 'icon' => 'sun'],
            ['name' => 'Dhuha', 'time' => $this->dhuha, 'icon' => 'sun'],
            ['name' => 'Dzuhur', 'time' => $this->dzuhur, 'icon' => 'sun'],
            ['name' => 'Ashar', 'time' => $this->ashar, 'icon' => 'cloud-sun'],
            ['name' => 'Maghrib', 'time' => $this->maghrib, 'icon' => 'sunset'],
            ['name' => 'Isya', 'time' => $this->isya, 'icon' => 'moon'],
        ];
    }

    public static function getMonthSummary(int $locationId, int $month, int $year)
    {
        return static::where('location_id', $locationId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();
    }
}
