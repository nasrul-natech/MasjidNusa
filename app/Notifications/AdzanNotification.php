<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class AdzanNotification extends Notification
{
    use Queueable;

    private const PRAYER_NAMES = [
        'imsak'   => 'Imsak',
        'subuh'   => 'Subuh',
        'dzuhur'  => 'Dzuhur',
        'ashar'   => 'Ashar',
        'maghrib' => 'Maghrib',
        'isya'    => 'Isya',
    ];

    public function __construct(
        private string $prayerKey,
        private string $time,
        private string $cityName,
        private int $minutesBefore = 0,
    ) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $name = self::PRAYER_NAMES[$this->prayerKey] ?? $this->prayerKey;

        if ($this->minutesBefore > 0) {
            $title = "⏰ {$this->minutesBefore} menit lagi — {$name}";
            $body  = "Waktu {$name} pukul {$this->time} ({$this->cityName}). Bersiap untuk sholat.";
        } else {
            $title = "🕌 Waktu {$name} — {$this->time}";
            $body  = "Telah masuk waktu {$name} untuk {$this->cityName}. Ayo segera sholat!";
        }

        return (new WebPushMessage())
            ->title($title)
            ->body($body)
            ->icon('/images/icons/icon-192.png')
            ->badge('/images/icons/badge-72.png')
            ->tag('adzan-' . $this->prayerKey)
            ->data([
                'type'   => 'adzan',
                'prayer' => $this->prayerKey,
                'time'   => $this->time,
                'url'    => '/jadwal-sholat',
            ])
            ->options(['TTL' => 300, 'urgency' => 'high'])
            ->action('Lihat Jadwal', 'open');
    }
}
