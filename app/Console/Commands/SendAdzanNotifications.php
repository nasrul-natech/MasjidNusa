<?php

namespace App\Console\Commands;

use App\Models\PrayerLocation;
use App\Models\PrayerTime;
use App\Models\SystemModule;
use App\Models\UserAdzanSetting;
use App\Notifications\AdzanNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendAdzanNotifications extends Command
{
    protected $signature = 'adzan:send';
    protected $description = 'Kirim push notification adzan jika waktu pengingat cocok dengan waktu sekarang';

    private const PRAYER_COLUMNS = [
        'imsak', 'subuh', 'dzuhur', 'ashar', 'maghrib', 'isya',
    ];

    public function handle(): int
    {
        if (!SystemModule::isActive('push_notification')) {
            $this->info('Modul push notification nonaktif. Tidak ada pengiriman.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach (PrayerLocation::all() as $location) {
            $timezone = $location->timezone();
            $localNow = now($timezone);
            $currentTime = $localNow->format('H:i');

            $prayerTime = PrayerTime::where('location_id', $location->id)
                ->where('date', $localNow->toDateString())
                ->first();

            if (!$prayerTime) {
                continue;
            }

            $settings = UserAdzanSetting::where('location_id', $location->id)
                ->where('push_enabled', true)
                ->with('user')
                ->get();

            if ($settings->isEmpty()) {
                continue;
            }

            foreach (self::PRAYER_COLUMNS as $prayer) {
                $rawTime = $prayerTime->{$prayer};
                if (!$rawTime) {
                    continue;
                }

                $time = substr((string) $rawTime, 0, 5);
                if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
                    continue;
                }

                $column = $prayer.'_enabled';

                foreach ($settings as $setting) {
                    if (!$setting->user || !$setting->user->is_active || !$setting->{$column}) {
                        continue;
                    }

                    $minutesBefore = (int) ($setting->minutes_before ?? 0);
                    $notifyTime = Carbon::createFromFormat('H:i', $time, $timezone)
                        ->subMinutes($minutesBefore)
                        ->format('H:i');

                    if ($notifyTime !== $currentTime) {
                        continue;
                    }

                    $recorded = DB::table('adzan_notification_deliveries')->insertOrIgnore([
                        'user_id' => $setting->user_id,
                        'location_id' => $location->id,
                        'prayer' => $prayer,
                        'sent_on' => $localNow->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($recorded === 0) {
                        continue;
                    }

                    try {
                        $cityName = $location->city_name ?: 'lokasi Anda';
                        $setting->user->notify(new AdzanNotification($prayer, $time, $cityName, $minutesBefore));
                        $sent++;
                    } catch (\Throwable $e) {
                        DB::table('adzan_notification_deliveries')
                            ->where('user_id', $setting->user_id)
                            ->where('location_id', $location->id)
                            ->where('prayer', $prayer)
                            ->where('sent_on', $localNow->toDateString())
                            ->delete();

                        Log::warning('Gagal kirim notif adzan', [
                            'user_id' => $setting->user_id,
                            'prayer' => $prayer,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        if ($sent > 0) {
            $this->info("Sent {$sent} adzan notification(s).");
        }

        return self::SUCCESS;
    }
}
