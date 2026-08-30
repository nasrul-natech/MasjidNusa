<?php

namespace Database\Seeders;

use App\Models\MosqueProfile;
use App\Models\MosqueHistory;
use App\Models\ManagementHistory;
use App\Models\OrganizationStructure;
use App\Models\PrayerSchedule;
use Illuminate\Database\Seeder;

class MosqueProfileSeeder extends Seeder
{
    public function run(): void
    {
        $mosque = MosqueProfile::firstOrCreate(
            ['name' => 'Masjid Nusa'],
            [
                'address' => 'Jl. Pejaten Raya No. 1, Jakarta Selatan, DKI Jakarta',
                'phone' => '021-7812345',
                'email' => 'masjid@masjidnusa.id',
                'description' => 'Masjid Nusa adalah masjid yang melayani kebutuhan spiritual jamaah dan masyarakat sekitar.',
                'vision' => 'Menjadi masjid yang memakmurkan dan menjadi pusat kegiatan keislaman yang modern dan berkelanjutan.',
                'mission' => "1. Menyelenggarakan ibadah yang tertib dan berkualitas\n2. Mengembangkan program pendidikan Islam\n3. Memfasilitasi kegiatan sosial dan kemasyarakatan\n4. Mengelola dana sosial dan donasi secara transparan",
            ]
        );

        // Prayer schedules
        $prayers = [
            ['prayer_name' => 'Subuh', 'time' => '04:30', 'type' => 'daily'],
            ['prayer_name' => 'Dzuhur', 'time' => '12:00', 'type' => 'daily'],
            ['prayer_name' => 'Ashar', 'time' => '15:15', 'type' => 'daily'],
            ['prayer_name' => 'Maghrib', 'time' => '18:00', 'type' => 'daily'],
            ['prayer_name' => 'Isya', 'time' => '19:15', 'type' => 'daily'],
            ['prayer_name' => "Jum'at", 'time' => '11:30', 'type' => 'jumat'],
        ];

        foreach ($prayers as $prayer) {
            PrayerSchedule::firstOrCreate(
                ['prayer_name' => $prayer['prayer_name'], 'type' => $prayer['type']],
                array_merge($prayer, ['is_active' => true])
            );
        }

        // Management history
        $mgmt = ManagementHistory::firstOrCreate(
            ['start_year' => 2023],
            [
                'title' => 'Pengurus Masjid Nusa Periode 2023-2026',
                'description' => 'Kepengurusan periode 2023-2026 berfokus pada digitalisasi dan modernisasi layanan masjid.',
                'start_year' => 2023,
                'end_year' => 2026,
                'is_current' => true,
            ]
        );

        // Organization structure
        $ketua = OrganizationStructure::firstOrCreate(
            ['management_history_id' => $mgmt->id, 'position' => 'Ketua Umum'],
            ['name' => 'H. Ahmad Fauzi', 'position' => 'Ketua Umum', 'sort_order' => 1]
        );

        OrganizationStructure::firstOrCreate(
            ['management_history_id' => $mgmt->id, 'position' => 'Sekretaris'],
            ['name' => 'Budi Santoso', 'position' => 'Sekretaris', 'parent_id' => $ketua->id, 'sort_order' => 2]
        );

        OrganizationStructure::firstOrCreate(
            ['management_history_id' => $mgmt->id, 'position' => 'Bendahara'],
            ['name' => 'Siti Rahayu', 'position' => 'Bendahara', 'parent_id' => $ketua->id, 'sort_order' => 3]
        );
    }
}
