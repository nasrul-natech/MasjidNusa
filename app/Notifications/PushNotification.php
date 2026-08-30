<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class PushNotification extends Notification
{
    public function __construct(
        protected string $title,
        protected string $body,
        protected ?string $url = null
    ) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $message = (new WebPushMessage())
            ->title($this->title)
            ->icon('/images/icons/icon-192.png')
            ->body($this->body);

        if ($this->url) {
            $message->action('Buka', 'open');
        }

        return $message;
    }
}
