<?php

namespace App\Console\Commands;

use App\Models\PrayerTime;
use App\Models\UserAdzanSetting;
use App\Notifications\AdzanNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAdzanNotifications extends Command
{
    protected $signature = 'adzan:send';
    protected $description = 'Kirim push notification adzan jika waktu sholat cocok dengan waktu sekarang';

    private const PRAYER_COLUMNS = [
        'imsak', 'subuh', 'dzuhur', 'ashar', 'maghrib', 'isya',
    ];

    public function handle(): int
    {
        $now = now();
        $currentTime = $now->format('H:i');
        $today = $now->toDateString();

        $prayerTimes = PrayerTime::where('date', $today)->get();

        if ($prayerTimes->isEmpty()) {
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($prayerTimes as $pt) {
            foreach (self::PRAYER_COLUMNS as $prayer) {
                $rawTime = $pt->{$prayer};
                if (!$rawTime) continue;

                $time = substr($rawTime, 0, 5);
                if ($time !== $currentTime) continue;

                $column = $prayer . '_enabled';
                $settings = UserAdzanSetting::where('location_id', $pt->location_id)
                    ->where($column, true)
                    ->where('push_enabled', true)
                    ->with('user')
                    ->get();

                foreach ($settings as $setting) {
                    if (!$setting->user) continue;

                    $minutesBefore = (int) ($setting->minutes_before ?? 0);
                    $notifyTime = \Carbon\Carbon::createFromFormat('H:i', $time)
                        ->subMinutes($minutesBefore)
                        ->format('H:i');

                    if ($notifyTime !== $currentTime) continue;

                    try {
                        $cityName = $setting->location?->city_name ?? 'lokasi Anda';
                        $setting->user->notify(new AdzanNotification($prayer, $time, $cityName, $minutesBefore));
                        $sent++;
                    } catch (\Throwable $e) {
                        Log::warning('Gagal kirim notif adzan', [
                            'user_id' => $setting->user_id,
                            'prayer'  => $prayer,
                            'error'   => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        if ($sent > 0) {
            $this->info("Sent {$sent} adzan notification(s) at {$currentTime}.");
        }

        return self::SUCCESS;
    }
}
