<?php

namespace App\Console\Commands;

use App\Support\AttendanceFeaturePermissions;
use Illuminate\Console\Command;

class ProvisionAttendanceFeatures extends Command
{
    protected $signature = 'features:provision-attendance-display';

    protected $description = 'Tambahkan izin fitur kehadiran/display tanpa mengganti izin lama atau mengaktifkan modul';

    public function handle(): int
    {
        AttendanceFeaturePermissions::provision();
        $this->info('Izin fitur ditambahkan pada peran yang sudah ada. Status modul tidak diubah.');

        return self::SUCCESS;
    }
}
