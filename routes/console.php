<?php

use App\Console\Commands\FetchPrayerSchedules;
use App\Console\Commands\SendAdzanNotifications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Setiap tanggal 25 pukul 02:00, fetch jadwal shalat bulan berikutnya untuk semua lokasi
Schedule::command(FetchPrayerSchedules::class)->monthlyOn(25, '02:00');

// Setiap menit, cek dan kirim notifikasi adzan
Schedule::command(SendAdzanNotifications::class)->everyMinute();
