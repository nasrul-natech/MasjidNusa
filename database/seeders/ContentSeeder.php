<?php

namespace Database\Seeders;

use App\Models\ActivityCategory;
use App\Models\Activity;
use App\Models\ArticleCategory;
use App\Models\Article;
use App\Models\Banner;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\SystemModule;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        // Activity categories
        $actCats = [
            ['name' => 'Kajian Islam', 'slug' => 'kajian-islam', 'color' => '#064e3b'],
            ['name' => 'Sosial', 'slug' => 'sosial', 'color' => '#2563EB'],
            ['name' => 'Peringatan Hari Besar', 'slug' => 'hari-besar', 'color' => '#D97706'],
            ['name' => 'Pendidikan', 'slug' => 'pendidikan', 'color' => '#7C3AED'],
            ['name' => 'Umum', 'slug' => 'umum', 'color' => '#6B7280'],
        ];

        foreach ($actCats as $cat) {
            ActivityCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Article categories
        $artCats = [
            ['name' => 'Dakwah', 'slug' => 'dakwah'],
            ['name' => 'Fiqih', 'slug' => 'fiqih'],
            ['name' => 'Motivasi', 'slug' => 'motivasi'],
            ['name' => 'Sejarah Islam', 'slug' => 'sejarah-islam'],
            ['name' => 'Info Masjid', 'slug' => 'info-masjid'],
        ];

        foreach ($artCats as $cat) {
            ArticleCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Sample articles
        $dakwahCat = ArticleCategory::where('slug', 'dakwah')->first();
        Article::firstOrCreate(
            ['slug' => 'keutamaan-shalat-berjamaah'],
            [
                'title' => 'Keutamaan Shalat Berjamaah di Masjid',
                'slug' => 'keutamaan-shalat-berjamaah',
                'content' => '<p>Shalat berjamaah di masjid memiliki keutamaan yang sangat besar dalam Islam. Rasulullah SAW bersabda bahwa shalat berjamaah lebih utama dari shalat sendirian sebanyak 27 derajat.</p><p>Mari kita tingkatkan kehadiran kita di masjid untuk shalat berjamaah.</p>',
                'excerpt' => 'Shalat berjamaah di masjid memiliki keutamaan yang sangat besar dalam Islam.',
                'category_id' => $dakwahCat?->id,
                'status' => 'publish',
                'published_at' => now()->subDays(3),
            ]
        );

        Article::firstOrCreate(
            ['slug' => 'pentingnya-sedekah'],
            [
                'title' => 'Pentingnya Sedekah dalam Kehidupan Muslim',
                'slug' => 'pentingnya-sedekah',
                'content' => '<p>Sedekah adalah salah satu ibadah yang sangat dianjurkan dalam Islam. Allah SWT berjanji akan membalas setiap sedekah dengan berlipat ganda.</p>',
                'excerpt' => 'Sedekah adalah salah satu ibadah yang sangat dianjurkan dalam Islam.',
                'category_id' => $dakwahCat?->id,
                'status' => 'publish',
                'published_at' => now()->subDays(7),
            ]
        );

        // Sample activities
        $kajianCat = ActivityCategory::where('slug', 'kajian-islam')->first();
        Activity::firstOrCreate(
            ['slug' => 'kajian-ramadhan-2025'],
            [
                'title' => 'Kajian Ramadhan 1446H',
                'slug' => 'kajian-ramadhan-2025',
                'description' => 'Kajian rutin menyambut bulan suci Ramadhan.',
                'content' => '<p>Kajian akan membahas persiapan menyambut Ramadhan dan amalan-amalan yang dianjurkan.</p>',
                'category_id' => $kajianCat?->id,
                'location' => 'Masjid Nusa',
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(10)->addHours(2),
                'status' => 'publish',
                'is_featured' => true,
                'show_popup' => true,
            ]
        );

        Activity::firstOrCreate(
            ['slug' => 'bakti-sosial-2025'],
            [
                'title' => 'Bakti Sosial Ramadhan 2025',
                'slug' => 'bakti-sosial-2025',
                'description' => 'Pembagian paket sembako untuk warga sekitar masjid.',
                'category_id' => ActivityCategory::where('slug', 'sosial')->first()?->id,
                'location' => 'Halaman Masjid Nusa',
                'start_date' => now()->addDays(15),
                'end_date' => now()->addDays(15)->addHours(4),
                'status' => 'publish',
                'is_featured' => false,
            ]
        );

        // Email templates
        $templates = [
            [
                'name' => 'Selamat Datang',
                'slug' => 'welcome',
                'subject' => 'Selamat Datang di MasjidNusa, {{name}}!',
                'body' => '<h2>Assalamu\'alaikum {{name}},</h2><p>Selamat datang di aplikasi MasjidNusa. Akun Anda telah berhasil dibuat.</p><p>Email: {{email}}</p><p>Silakan login dan jelajahi fitur-fitur yang tersedia.</p><br><p>Wassalamu\'alaikum Warahmatullahi Wabarakatuh</p><p>Tim MasjidNusa</p>',
                'placeholders' => ['name', 'email'],
                'is_active' => true,
            ],
            [
                'name' => 'Reset Password',
                'slug' => 'password_reset',
                'subject' => 'Reset Password Akun MasjidNusa',
                'body' => '<h2>Assalamu\'alaikum {{name}},</h2><p>Kami menerima permintaan reset password untuk akun Anda.</p><p>Klik tombol di bawah untuk mereset password:</p><p><a href="{{reset_url}}" style="background:#064e3b;color:white;padding:12px 24px;text-decoration:none;border-radius:8px;">Reset Password</a></p><p>Link ini berlaku selama 60 menit.</p><p>Jika Anda tidak meminta reset password, abaikan email ini.</p>',
                'placeholders' => ['name', 'reset_url'],
                'is_active' => true,
            ],
            [
                'name' => 'Password Berhasil Diubah',
                'slug' => 'password_changed',
                'subject' => 'Password Akun MasjidNusa Berhasil Diubah',
                'body' => '<h2>Assalamu\'alaikum {{name}},</h2><p>Password akun Anda di MasjidNusa berhasil diubah pada <strong>{{date}}</strong>.</p><p>Jika Anda tidak melakukan perubahan ini, segera hubungi administrator atau reset password Anda kembali.</p><br><p>Wassalamu\'alaikum Warahmatullahi Wabarakatuh</p><p>Tim MasjidNusa</p>',
                'placeholders' => ['name', 'date'],
                'is_active' => true,
            ],
            [
                'name' => 'Pembayaran Berhasil',
                'slug' => 'payment_success',
                'subject' => 'Konfirmasi Donasi - MasjidNusa',
                'body' => '<h2>Jazakallah Khairan, {{name}}!</h2><p>Donasi Anda telah berhasil diterima dan diverifikasi.</p><table><tr><td>Kode Donasi</td><td>{{donation_code}}</td></tr><tr><td>Jumlah</td><td>Rp {{amount}}</td></tr><tr><td>Kampanye</td><td>{{campaign}}</td></tr><tr><td>Tanggal</td><td>{{date}}</td></tr></table><p>Semoga Allah SWT membalas kebaikan Anda dengan berlipat ganda. Aamiin.</p>',
                'placeholders' => ['name', 'donation_code', 'amount', 'campaign', 'date'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::firstOrCreate(['slug' => $template['slug']], $template);
        }

        // Settings
        $settings = [
            ['key' => 'app_name', 'value' => 'MasjidNusa', 'group' => 'branding', 'label' => 'Nama Aplikasi'],
            ['key' => 'app_tagline', 'value' => 'Menghubungkan Masjid, Umat & Kebaikan', 'group' => 'branding', 'label' => 'Tagline'],
            ['key' => 'mosque_name', 'value' => 'Masjid Nusa', 'group' => 'general', 'label' => 'Nama Masjid'],
            ['key' => 'primary_color', 'value' => '#064e3b', 'group' => 'branding', 'label' => 'Warna Utama'],
            ['key' => 'accent_color', 'value' => '#c59d3f', 'group' => 'branding', 'label' => 'Warna Aksen'],
            ['key' => 'logo_path', 'value' => null, 'group' => 'branding', 'label' => 'Logo Path'],
            ['key' => 'logo_icon_path', 'value' => null, 'group' => 'branding', 'label' => 'Logo Icon Path'],
            ['key' => 'favicon_path', 'value' => null, 'group' => 'branding', 'label' => 'Favicon Path'],
            ['key' => 'items_per_page', 'value' => '15', 'group' => 'display', 'label' => 'Item Per Halaman'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        // System modules
        $modules = [
            ['name' => 'Donasi', 'slug' => 'donasi', 'description' => 'Modul donasi dan pembayaran', 'is_active' => true, 'is_core' => false],
            ['name' => 'Notifikasi Push', 'slug' => 'push_notification', 'description' => 'Modul push notification PWA', 'is_active' => true, 'is_core' => false],
            ['name' => 'Popup Iklan', 'slug' => 'popup_ads', 'description' => 'Modul popup iklan kegiatan', 'is_active' => true, 'is_core' => false],
            ['name' => 'Kegiatan', 'slug' => 'kegiatan', 'description' => 'Modul kegiatan masjid', 'is_active' => true, 'is_core' => true],
            ['name' => 'Dakwah & Artikel', 'slug' => 'artikel', 'description' => 'Modul artikel dan dakwah', 'is_active' => true, 'is_core' => true],
        ];

        foreach ($modules as $module) {
            SystemModule::firstOrCreate(['slug' => $module['slug']], $module);
        }
    }
}
