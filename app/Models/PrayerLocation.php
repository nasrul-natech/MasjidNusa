<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerLocation extends Model
{
    protected $fillable = [
        'province_id', 'province_name', 'equran_province',
        'city_id', 'city_name', 'equran_city',
        'latitude', 'longitude',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function prayerTimes()
    {
        return $this->hasMany(PrayerTime::class, 'location_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->city_name}, {$this->province_name}";
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first() ?? static::first();
    }

    public function setAsDefault(): void
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    public function getScheduleForDate($date = null)
    {
        $date = $date ?? now()->toDateString();
        return $this->prayerTimes()->where('date', $date)->first();
    }
}
