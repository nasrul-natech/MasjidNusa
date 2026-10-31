<?php

namespace App\Support;

use App\Models\SystemModule;

final class NewFeature
{
    public static function enabled(string $slug): bool
    {
        // New features fail closed; legacy module defaults remain unchanged.
        if (! SystemModule::where('slug', $slug)->where('is_active', true)->exists()) {
            return false;
        }

        return $slug !== 'attendance_rewards' || self::enabled('activity_attendance');
    }
}
