<?php

namespace App\Console\Commands;

use App\Models\PrayerLocation;
use App\Services\PrayerScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FetchPrayerSchedules extends Command
{
    protected $signature = 'prayer:fetch-schedules
                            {--month= : Bulan yang ingin di-fetch (default: bulan depan)}
                            {--year=  : Tahun yang ingin di-fetch (default: tahun sesuai bulan depan)}
                            {--location= : ID lokasi tertentu (default: semua lokasi)}';

    protected $description = 'Auto-fetch jadwal shalat dari Aladhan API (metode Kemenag RI) untuk semua lokasi terdaftar';

    public function __construct(private readonly PrayerScheduleService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $nextMonth = Carbon::now()->addMonth();
        $month = (int) ($this->option('month') ?: $nextMonth->month);
        $year  = (int) ($this->option('year')  ?: $nextMonth->year);

        $locationId = $this->option('location');

        $locations = $locationId
            ? PrayerLocation::where('id', $locationId)->get()
            : PrayerLocation::all();

        if ($locations->isEmpty()) {
            $this->warn('Tidak ada lokasi terdaftar.');
            return self::SUCCESS;
        }

        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
        $this->info("Mengambil jadwal shalat untuk {$monthName} ({$locations->count()} lokasi)...");

        $success = 0;
        $failed  = 0;

        foreach ($locations as $location) {
            $this->line("  → {$location->full_name}...", null, 'v');
            try {
                $count = $this->service->fetchAndSave($location, $month, $year);
                $this->info("  ✓ {$location->full_name}: {$count} hari disimpan.");
                $success++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$location->full_name}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Selesai. Berhasil: {$success}, Gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
