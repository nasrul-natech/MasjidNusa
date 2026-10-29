<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\EmailTemplate;
use App\Models\SmtpSetting;
use App\Models\SystemModule;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class NotificationService
{
    public function createNotification(User $user, string $title, string $body, string $category = 'general', ?string $link = null): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'category' => $category,
            'link' => $link,
        ]);
    }

    public function notifyAllUsers(string $title, string $body, string $category = 'general', ?string $link = null): void
    {
        User::chunk(100, function ($users) use ($title, $body, $category, $link) {
            foreach ($users as $user) {
                $this->createNotification($user, $title, $body, $category, $link);
            }
        });
    }

    public function sendPushNotification(User $user, string $title, string $body, ?string $link = null): void
    {
        if (!$user->is_active || !SystemModule::isActive('push_notification')) {
            return;
        }

        try {
            $user->notify(new \App\Notifications\PushNotification($title, $body, $link));
        } catch (\Exception $e) {
            Log::warning('Push notification failed for user ' . $user->id . ': ' . $e->getMessage());
        }
    }

    public function sendWelcome(User $user): void
    {
        $this->createNotification(
            $user,
            'Selamat Datang di MasjidNusa!',
            'Akun Anda berhasil dibuat. Jelajahi fitur-fitur yang tersedia.',
            'system',
            '/'
        );

        $this->sendEmail($user->email, 'welcome', [
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function sendPaymentSuccess(Donation $donation): void
    {
        if (!$donation->user) return;

        $this->createNotification(
            $donation->user,
            'Donasi Berhasil Diverifikasi!',
            "Donasi Anda sebesar Rp " . number_format($donation->amount, 0, ',', '.') . " telah dikonfirmasi.",
            'payment',
            route('user.donations.history')
        );

        $this->sendPushNotification(
            $donation->user,
            '✅ Donasi Berhasil!',
            "Jazakallah khairan! Donasi Rp " . number_format($donation->amount, 0, ',', '.') . " telah diterima.",
            route('user.donations.history')
        );

        $this->sendEmail($donation->user->email, 'payment_success', [
            'name' => $donation->user->name,
            'donation_code' => $donation->donation_code,
            'amount' => number_format($donation->amount, 0, ',', '.'),
            'campaign' => $donation->campaign?->title ?? 'Umum',
            'date' => $donation->updated_at->format('d F Y H:i'),
        ]);
    }

    public function sendNewActivityNotification(User $user, string $activityTitle, string $link): void
    {
        $this->createNotification(
            $user,
            'Kegiatan Baru: ' . $activityTitle,
            'Ada kegiatan baru yang akan segera diselenggarakan.',
            'activity',
            $link
        );

        $this->sendPushNotification($user, '📅 Kegiatan Baru', $activityTitle, $link);
    }

    public function sendEmail(string $toEmail, string $templateSlug, array $data = []): void
    {
        $template = EmailTemplate::findBySlug($templateSlug);
        if (!$template) {
            Log::warning("Email template '{$templateSlug}' not found, skipping email to {$toEmail}");
            return;
        }

        $smtp = SmtpSetting::active();
        if (!$smtp) {
            Log::warning("SMTP not configured or inactive, skipping email to {$toEmail}");
            return;
        }

        $this->applySmtpConfig($smtp);

        try {
            $body = $template->render($data);
            $subject = str_replace(array_map(fn($k) => '{{' . $k . '}}', array_keys($data)), array_values($data), $template->subject);

            Mail::html($body, function ($msg) use ($toEmail, $subject, $smtp) {
                $msg->to($toEmail)->subject($subject);
                $msg->from($smtp->sender_email, $smtp->sender_name);
            });

            Log::info("Email sent to {$toEmail} using template '{$templateSlug}'");
        } catch (\Exception $e) {
            Log::error("Email send failed to {$toEmail}: " . $e->getMessage());
        }
    }

    private function applySmtpConfig(SmtpSetting $smtp): void
    {
        $encryption = $smtp->encryption === 'none' ? '' : $smtp->encryption;

        config([
            'mail.default'                       => 'smtp',
            'mail.mailers.smtp.transport'        => 'smtp',
            'mail.mailers.smtp.host'             => $smtp->host,
            'mail.mailers.smtp.port'             => $smtp->port,
            'mail.mailers.smtp.username'         => $smtp->username,
            'mail.mailers.smtp.password'         => $smtp->password,
            'mail.mailers.smtp.encryption'       => $encryption ?: null,
            'mail.from.address'                  => $smtp->sender_email,
            'mail.from.name'                     => $smtp->sender_name,
        ]);

        Mail::purge('smtp');
    }
}
