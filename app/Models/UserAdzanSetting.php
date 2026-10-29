<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAdzanSetting extends Model
{
    protected $fillable = [
        'user_id', 'location_id',
        'imsak_enabled', 'subuh_enabled', 'dzuhur_enabled',
        'ashar_enabled', 'maghrib_enabled', 'isya_enabled',
        'sound_enabled', 'sound_type', 'volume',
        'push_enabled', 'minutes_before',
    ];

    protected $casts = [
        'imsak_enabled'  => 'boolean',
        'subuh_enabled'  => 'boolean',
        'dzuhur_enabled' => 'boolean',
        'ashar_enabled'  => 'boolean',
        'maghrib_enabled' => 'boolean',
        'isya_enabled'   => 'boolean',
        'sound_enabled'  => 'boolean',
        'push_enabled'   => 'boolean',
        'volume'         => 'integer',
        'minutes_before' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(PrayerLocation::class, 'location_id');
    }

    public function getEnabledPrayers(): array
    {
        $prayers = [];
        foreach (['imsak', 'subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $p) {
            if ($this->{$p . '_enabled'}) {
                $prayers[] = $p;
            }
        }
        return $prayers;
    }

    public static function getOrCreateForUser(int $userId): self
    {
        $settings = static::firstOrCreate(
            ['user_id' => $userId],
            ['location_id' => PrayerLocation::getDefault()?->id]
        );

        return $settings->wasRecentlyCreated ? $settings->refresh() : $settings;
    }
}
