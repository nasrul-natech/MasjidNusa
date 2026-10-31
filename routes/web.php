<?php

require __DIR__.'/activity-attendance.php';
require __DIR__.'/attendance-rewards.php';
require __DIR__.'/mosque-display.php';

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ActivityController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\DonationController;
use App\Http\Controllers\Public\MosqueInfoController;
use App\Http\Controllers\Public\InfoSistemController;
use App\Http\Controllers\Public\ManifestController;
use App\Http\Controllers\Public\DoaController;
use App\Http\Controllers\Public\QuranController;
use App\Http\Controllers\Public\PrayerScheduleController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\DonationUserController;
use App\Http\Controllers\User\AdzanSettingController;
use App\Http\Controllers\Admin\Operasional\DashboardController as OpsDashboard;
use App\Http\Controllers\Admin\Operasional\MosqueProfileController;
use App\Http\Controllers\Admin\Operasional\MosqueHistoryController;
use App\Http\Controllers\Admin\Operasional\ManagementHistoryController;
use App\Http\Controllers\Admin\Operasional\OrganizationController;
use App\Http\Controllers\Admin\Operasional\PrayerScheduleAdminController;
use App\Http\Controllers\Admin\Operasional\DoaAdminController;
use App\Http\Controllers\Admin\Operasional\DoaCategoryAdminController;
use App\Http\Controllers\Admin\Keuangan\DashboardController as KeuDashboard;
use App\Http\Controllers\Admin\Keuangan\CampaignAdminController;
use App\Http\Controllers\Admin\Keuangan\DonationCategoryController;
use App\Http\Controllers\Admin\Keuangan\DonationAdminController;
use App\Http\Controllers\Admin\Keuangan\PaymentVerificationController;
use App\Http\Controllers\Admin\Keuangan\BankAccountController;
use App\Http\Controllers\Admin\Keuangan\PaymentConfigController;
use App\Http\Controllers\Admin\Keuangan\DuitkuAdminController;
use App\Http\Controllers\Admin\Keuangan\ReportController;
use App\Http\Controllers\Admin\MediaIT\DashboardController as MediaDashboard;
use App\Http\Controllers\Admin\MediaIT\ActivityAdminController;
use App\Http\Controllers\Admin\MediaIT\ArticleAdminController;
use App\Http\Controllers\Admin\MediaIT\BannerController;
use App\Http\Controllers\Admin\MediaIT\PopupAdController;
use App\Http\Controllers\Admin\MediaIT\NotificationAdminController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashboard;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\SmtpController;
use App\Http\Controllers\SuperAdmin\EmailTemplateController;
use App\Http\Controllers\SuperAdmin\PaymentRulesController;
use App\Http\Controllers\SuperAdmin\SettingsController;
use App\Http\Controllers\SuperAdmin\ModuleController;
use App\Http\Controllers\SuperAdmin\BrandingController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Webhook\DuitkuWebhookController;

// ===== PUBLIC ROUTES =====
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jadwal-sholat', [PrayerScheduleController::class, 'index'])->name('jadwal-sholat');
Route::get('/api/prayer-today', [PrayerScheduleController::class, 'todayJson'])->name('prayer.today.json');
Route::get('/api/prayer-nearest', [PrayerScheduleController::class, 'nearestJson'])->name('prayer.nearest.json');
Route::get('/api/adzan/schedule-today', [AdzanSettingController::class, 'todayScheduleJson'])->name('adzan.schedule.json');
Route::get('/doa', [DoaController::class, 'index'])->name('doa.index');
Route::get('/doa/{slug}', [DoaController::class, 'show'])->name('doa.show');
Route::post('/doa/{doa}/favorite', [DoaController::class, 'toggleFavorite'])->name('doa.favorite')->middleware('auth');
Route::get('/kegiatan', [ActivityController::class, 'index'])->name('kegiatan.index');
Route::get('/kegiatan/{slug}', [ActivityController::class, 'show'])->name('kegiatan.show');
Route::get('/dakwah', [ArticleController::class, 'index'])->name('dakwah.index');
Route::get('/dakwah/{slug}', [ArticleController::class, 'show'])->name('dakwah.show');
Route::middleware('module:donasi')->group(function () {
    Route::get('/donasi', [DonationController::class, 'index'])->name('donasi.index');
    Route::get('/donasi/{slug}', [DonationController::class, 'show'])->name('donasi.show');
});
Route::get('/info-masjid', [MosqueInfoController::class, 'index'])->name('info-masjid.index');
Route::get('/info-masjid/sejarah', [MosqueInfoController::class, 'history'])->name('info-masjid.history');
Route::get('/info-masjid/kepengurusan', [MosqueInfoController::class, 'management'])->name('info-masjid.management');
Route::get('/info-masjid/organisasi', [MosqueInfoController::class, 'organization'])->name('info-masjid.organization');
Route::get('/info-sistem', [InfoSistemController::class, 'index'])->name('info-sistem');
Route::get('/manifest.webmanifest', ManifestController::class)->name('manifest');
// ===== AL-QURAN =====
Route::prefix('quran')->name('quran.')->group(function () {
    Route::get('/', [QuranController::class, 'index'])->name('index');
    Route::get('/surah/{number}', [QuranController::class, 'surah'])->name('surah')->where('number', '[0-9]+');
    Route::get('/juz/{number}', [QuranController::class, 'juz'])->name('juz')->where('number', '[0-9]+');
    Route::get('/ayat/{surah}/{verse}', [QuranController::class, 'verse'])->name('verse');
    Route::get('/cari', [QuranController::class, 'search'])->name('search');
});
Route::middleware('auth')->prefix('quran')->name('quran.')->group(function () {
    Route::post('/bookmark', [QuranController::class, 'bookmark'])->name('bookmark');
    Route::post('/last-read', [QuranController::class, 'setLastRead'])->name('last-read');
    Route::get('/khatam', [QuranController::class, 'khatamIndex'])->name('khatam.index');
    Route::post('/khatam', [QuranController::class, 'khatamStore'])->name('khatam.store');
    Route::get('/khatam/{id}', [QuranController::class, 'khatamShow'])->name('khatam.show');
    Route::post('/khatam/{id}/progress', [QuranController::class, 'khatamProgress'])->name('khatam.progress');
});

Route::get('/offline', function () {
    return view('public.offline');
})->name('offline');

// ===== AUTH ROUTES =====
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::middleware('pending-two-factor')->group(function () {
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verifyChallenge'])->middleware('throttle:10,1');
    Route::get('/two-factor/setup', [TwoFactorController::class, 'showSetup'])->name('two-factor.setup');
    Route::post('/two-factor/setup', [TwoFactorController::class, 'confirmSetup'])->middleware('throttle:10,1');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ===== USER / JAMAAH ROUTES =====
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('/adzan-settings', [AdzanSettingController::class, 'index'])->name('adzan.settings');
    Route::post('/adzan-settings', [AdzanSettingController::class, 'update'])->name('adzan.settings.update');
    Route::post('/adzan-settings/sync-location', [AdzanSettingController::class, 'syncLocation'])->name('adzan.settings.sync-location');

    Route::middleware('module:donasi')->group(function () {
        Route::post('/donasi/submit', [DonationUserController::class, 'quickDonate'])->name('donations.quick');
        Route::post('/donasi/{slug}', [DonationUserController::class, 'store'])->name('donations.store');
        Route::get('/riwayat-donasi', [DonationUserController::class, 'history'])->name('donations.history');
        Route::post('/upload-bukti/{transactionId}', [DonationUserController::class, 'uploadProof'])->name('donations.upload-proof');
    });
});

// ===== ADMIN OPERASIONAL =====
Route::middleware(['auth', 'two-factor', 'role:admin_operasional,super_admin'])->prefix('admin/operasional')->name('admin.operasional.')->group(function () {
    Route::middleware('permission:access_admin_operasional')->group(function () {
        Route::get('/dashboard', [OpsDashboard::class, 'index'])->name('dashboard');
    });

    Route::middleware('permission:manage_mosque_profile')->group(function () {
        Route::resource('mosque-profile', MosqueProfileController::class)->only(['index', 'edit', 'update']);
    });
    Route::middleware('permission:manage_mosque_history')->group(function () {
        Route::resource('mosque-history', MosqueHistoryController::class);
    });
    Route::middleware('permission:manage_management_history')->group(function () {
        Route::resource('management-history', ManagementHistoryController::class);
    });
    Route::middleware('permission:manage_organization_structure')->group(function () {
        Route::resource('organization', OrganizationController::class);
    });

    Route::middleware('permission:manage_prayer_schedule')->group(function () {
        Route::resource('prayer-schedule', PrayerScheduleAdminController::class);
        Route::get('/prayer-schedule-locations', [PrayerScheduleAdminController::class, 'locations'])->name('prayer-schedule.locations');
        Route::post('/prayer-schedule-locations', [PrayerScheduleAdminController::class, 'storeLocation'])->name('prayer-schedule.locations.store');
        Route::delete('/prayer-schedule-locations/{location}', [PrayerScheduleAdminController::class, 'destroyLocation'])->name('prayer-schedule.locations.destroy');
        Route::post('/prayer-schedule-locations/{location}/default', [PrayerScheduleAdminController::class, 'setDefaultLocation'])->name('prayer-schedule.locations.default');
        Route::get('/prayer-schedule-import', [PrayerScheduleAdminController::class, 'importForm'])->name('prayer-schedule.import-form');
        Route::post('/prayer-schedule-import', [PrayerScheduleAdminController::class, 'import'])->name('prayer-schedule.import');
        Route::post('/prayer-schedule-fetch-api', [PrayerScheduleAdminController::class, 'fetchFromApi'])->name('prayer-schedule.fetch-api');
        Route::delete('/prayer-schedule-destroy-month', [PrayerScheduleAdminController::class, 'destroyMonth'])->name('prayer-schedule.destroy-month');
        Route::get('/prayer-schedule-api/provinces', [PrayerScheduleAdminController::class, 'apiProvinces'])->name('prayer-schedule.api.provinces');
        Route::get('/prayer-schedule-api/cities', [PrayerScheduleAdminController::class, 'apiCities'])->name('prayer-schedule.api.cities');
    });

    Route::middleware('permission:manage_general_info')->group(function () {
        Route::resource('doa', DoaAdminController::class);
        Route::resource('doa-category', DoaCategoryAdminController::class)->except(['show']);
    });
});

// ===== ADMIN KEUANGAN =====
Route::middleware(['auth', 'two-factor', 'role:admin_keuangan,super_admin', 'module:donasi'])->prefix('admin/keuangan')->name('admin.keuangan.')->group(function () {
    Route::middleware('permission:access_admin_keuangan')->group(function () {
        Route::get('/dashboard', [KeuDashboard::class, 'index'])->name('dashboard');
    });

    Route::middleware('permission:manage_donations')->group(function () {
        Route::resource('campaigns', CampaignAdminController::class);
        Route::post('/campaigns/{campaign}/toggle-featured', [CampaignAdminController::class, 'toggleFeatured'])->name('campaigns.toggle-featured');
        Route::post('/campaigns/{campaign}/adjust-amount', [CampaignAdminController::class, 'adjustAmount'])->name('campaigns.adjust-amount');
        Route::post('/campaigns/{campaign}/sync-amount', [CampaignAdminController::class, 'syncAmount'])->name('campaigns.sync-amount');
        Route::resource('donation-categories', DonationCategoryController::class)->except(['show', 'create', 'edit']);
        Route::resource('donations', DonationAdminController::class)->only(['index', 'show']);
    });

    Route::middleware('permission:verify_payments')->group(function () {
        Route::get('/verify-payment', [PaymentVerificationController::class, 'index'])->name('verify-payment.index');
        Route::post('/verify-payment/{id}/approve', [PaymentVerificationController::class, 'approve'])->name('verify-payment.approve');
        Route::post('/verify-payment/{id}/reject', [PaymentVerificationController::class, 'reject'])->name('verify-payment.reject');
    });

    Route::middleware('permission:manage_bank_accounts')->group(function () {
        Route::resource('bank-accounts', BankAccountController::class);
    });
    Route::middleware('permission:manage_payment_config')->group(function () {
        Route::get('/payment-config', [PaymentConfigController::class, 'index'])->name('payment-config.index');
        Route::post('/payment-config', [PaymentConfigController::class, 'update'])->name('payment-config.update');
    });
    Route::middleware('permission:manage_duitku')->group(function () {
        Route::get('/duitku', [DuitkuAdminController::class, 'index'])->name('duitku.index');
        Route::post('/duitku', [DuitkuAdminController::class, 'update'])->name('duitku.update');
    });
    Route::middleware('permission:view_donation_reports')->group(function () {
        Route::get('/reports/donations', [ReportController::class, 'donations'])->name('reports.donations');
        Route::get('/reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
    });
    Route::middleware('permission:export_reports')->group(function () {
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
});

// ===== ADMIN MEDIA IT =====
Route::middleware(['auth', 'two-factor', 'role:admin_media_it,super_admin'])->prefix('admin/media-it')->name('admin.media-it.')->group(function () {
    Route::middleware('permission:access_admin_media_it')->group(function () {
        Route::get('/dashboard', [MediaDashboard::class, 'index'])->name('dashboard');
    });

    Route::middleware('permission:manage_activities')->group(function () {
        Route::resource('activities', ActivityAdminController::class);
        Route::post('/activities/{id}/publish', [ActivityAdminController::class, 'publish'])->name('activities.publish');
    });
    Route::middleware('permission:manage_articles')->group(function () {
        Route::resource('articles', ArticleAdminController::class);
        Route::post('/articles/{id}/publish', [ArticleAdminController::class, 'publish'])->name('articles.publish');
    });
    Route::middleware('permission:manage_banners')->group(function () {
        Route::resource('banners', BannerController::class);
    });
    Route::middleware(['permission:manage_popup_ads', 'module:popup_ads'])->group(function () {
        Route::resource('popup-ads', PopupAdController::class);
        Route::post('/popup-ads/{popupAd}/toggle', [PopupAdController::class, 'toggleActive'])->name('popup-ads.toggle');
    });
    Route::middleware('permission:manage_home_content')->group(function () {
        Route::get('/notifications', [NotificationAdminController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/send', [NotificationAdminController::class, 'send'])->name('notifications.send');
    });
});

// ===== SUPER ADMIN =====
Route::middleware(['auth', 'two-factor', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperDashboard::class, 'index'])->name('dashboard');

    Route::middleware('permission:manage_users')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    });

    Route::middleware('permission:manage_roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles/{id}/assign', [RoleController::class, 'assign'])->name('roles.assign');
    });

    Route::middleware('permission:manage_smtp')->group(function () {
        Route::get('/smtp', [SmtpController::class, 'index'])->name('smtp.index');
        Route::post('/smtp', [SmtpController::class, 'update'])->name('smtp.update');
        Route::post('/smtp/test', [SmtpController::class, 'test'])->name('smtp.test');
    });

    Route::middleware('permission:manage_email_templates')->group(function () {
        Route::resource('email-templates', EmailTemplateController::class);
    });

    Route::middleware('permission:manage_payment_rules')->group(function () {
        Route::get('/payment-rules', [PaymentRulesController::class, 'index'])->name('payment-rules.index');
        Route::post('/payment-rules', [PaymentRulesController::class, 'update'])->name('payment-rules.update');
    });

    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('permission:manage_modules')->group(function () {
        Route::resource('modules', ModuleController::class)->only(['index', 'update']);
        Route::post('/modules/{id}/toggle', [ModuleController::class, 'toggle'])->name('modules.toggle');
    });

    Route::middleware('permission:manage_branding')->group(function () {
        Route::get('/branding', [BrandingController::class, 'index'])->name('branding.index');
        Route::post('/branding', [BrandingController::class, 'update'])->name('branding.update');
    });

    Route::middleware('permission:view_audit_log')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
        Route::post('/audit-log/purge', [AuditLogController::class, 'purgeLogs'])->name('audit-log.purge');
    });
});

// ===== PUSH SUBSCRIPTION API =====
Route::middleware('auth')->get('/bukti-transfer/{proof}', [DonationUserController::class, 'showProof'])->name('donations.proof');

Route::middleware('auth')->delete('/api/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('api.push.unsubscribe');
Route::middleware(['auth', 'module:push_notification'])->post('/api/push/subscribe', [PushSubscriptionController::class, 'store'])->name('api.push.subscribe');

// ===== WEBHOOKS =====
Route::post('/webhook/duitku/callback', [DuitkuWebhookController::class, 'callback'])->name('webhook.duitku.callback');
Route::get('/webhook/duitku/return', [DuitkuWebhookController::class, 'return'])->name('webhook.duitku.return');
