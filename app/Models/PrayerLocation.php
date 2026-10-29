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

    public function timezone(): string
    {
        $name = mb_strtolower(trim((string) $this->province_name));

        if ($name === '') {
            return (string) config('app.timezone', 'Asia/Jakarta');
        }

        $wit = [
            'maluku', 'maluku utara', 'papua', 'papua barat', 'papua selatan',
            'papua tengah', 'papua pegunungan', 'papua barat daya',
        ];
        $wita = [
            'bali', 'nusa tenggara barat', 'nusa tenggara timur',
            'kalimantan selatan', 'kalimantan timur', 'kalimantan utara',
            'sulawesi utara', 'sulawesi tengah', 'sulawesi selatan',
            'sulawesi tenggara', 'sulawesi barat', 'gorontalo',
        ];

        if (in_array($name, $wit, true) || str_contains($name, 'papua') || str_contains($name, 'maluku')) {
            return 'Asia/Jayapura';
        }

        if (in_array($name, $wita, true) || str_contains($name, 'sulawesi') || str_contains($name, 'nusa tenggara') || $name === 'bali' || $name === 'gorontalo') {
            return 'Asia/Makassar';
        }

        return 'Asia/Jakarta';
    }
}
