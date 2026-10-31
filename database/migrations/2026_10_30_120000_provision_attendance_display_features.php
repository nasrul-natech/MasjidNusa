<?php

use App\Support\AttendanceFeaturePermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['activity_attendance' => 'Check-in Kegiatan', 'attendance_rewards' => 'Poin Kehadiran Jamaah', 'mosque_display' => 'Display Masjid'] as $slug => $name) {
            DB::table('system_modules')->insertOrIgnore([
                'slug' => $slug, 'name' => $name, 'description' => $name,
                'is_active' => false, 'is_core' => false, 'created_at' => now(), 'updated_at' => now(),
            ]);
            Cache::forget("module_{$slug}");
        }

        AttendanceFeaturePermissions::provision();
    }

    public function down(): void
    {
        // Preserve permissions and flags on rollback; disable features through module controls.
    }
};
