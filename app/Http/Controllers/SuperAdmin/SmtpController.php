<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport;

class SmtpController extends Controller
{
    public function index()
    {
        $smtp = SmtpSetting::first();
        return view('super-admin.smtp.index', compact('smtp'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'host' => ['required', 'string'],
            'port' => ['required', 'integer'],
            'username' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'encryption' => ['required', 'in:tls,ssl,none'],
            'sender_name' => ['required', 'string'],
            'sender_email' => ['required', 'email'],
            'is_active' => ['boolean'],
        ]);

        $existing = SmtpSetting::first();
        if ($existing && empty($data['password'])) {
            unset($data['password']);
        }

        SmtpSetting::updateOrCreate(['id' => 1], $data);
        return back()->with('success', 'Konfigurasi SMTP berhasil disimpan.');
    }

    public function test(Request $request)
    {
        $data = $request->validate(['test_email' => ['required', 'email']]);

        $smtp = SmtpSetting::first();

        if (!$smtp) {
            return back()->with('error', 'Konfigurasi SMTP belum diisi. Simpan konfigurasi terlebih dahulu.');
        }

        if (!$smtp->host || !$smtp->sender_email) {
            return back()->with('error', 'SMTP Host dan Email Pengirim harus diisi.');
        }

        $this->applySmtpConfig($smtp);

        try {
            Mail::raw(
                "Ini adalah test email dari MasjidNusa.\n\nSMTP berfungsi dengan baik!\n\nWaktu: " . now()->format('d M Y H:i:s'),
                function ($msg) use ($data, $smtp) {
                    $msg->to($data['test_email'])
                        ->subject('Test SMTP - MasjidNusa')
                        ->from($smtp->sender_email, $smtp->sender_name);
                }
            );

            return back()->with('success', 'Test email berhasil dikirim ke ' . $data['test_email'] . '. Periksa inbox (dan folder spam).');
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            return back()->with('error', 'Koneksi SMTP gagal: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
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
