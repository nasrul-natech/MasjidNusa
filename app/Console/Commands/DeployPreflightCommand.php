<?php

namespace App\Console\Commands;

use App\Support\GoogleAuth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class DeployPreflightCommand extends Command
{
    protected $signature = 'deploy:preflight';

    protected $description = 'Check deployment readiness and log potential hosting issues';

    private const LOG_PATH = '/Users/nasrul/Herd/update/MasjidNusa/.cursor/debug-c5a801.log';

    public function handle(): int
    {
        $this->info('Running deployment preflight checks…');
        $issues = 0;

        $checks = [
            fn () => $this->checkEnvConfig(),
            fn () => $this->checkAppKey(),
            fn () => $this->checkStorage(),
            fn () => $this->checkDatabaseDrivers(),
            fn () => $this->checkMigrations(),
            fn () => $this->checkViteBuild(),
            fn () => $this->checkPublicAssets(),
            fn () => $this->checkIntegrations(),
            fn () => $this->checkUrlGeneration(),
            fn () => $this->checkScheduler(),
            fn () => $this->checkTrustProxies(),
        ];

        foreach ($checks as $check) {
            $issues += $check();
        }

        $this->log('summary', 'Preflight complete', [
            'issueCount' => $issues,
            'appEnv'     => config('app.env'),
            'appDebug'   => config('app.debug'),
            'appUrl'     => config('app.url'),
        ], 'summary');

        if ($issues === 0) {
            $this->info('No blocking issues detected locally. Review warnings before pushing to hosting.');
            return self::SUCCESS;
        }

        $this->warn("Found {$issues} issue(s). See debug log for details.");
        return self::FAILURE;
    }

    private function checkEnvConfig(): int
    {
        $issues = 0;
        $env = config('app.env');
        $debug = config('app.debug');

        if ($env !== 'production') {
            $this->warn("APP_ENV={$env} — set APP_ENV=production on hosting.");
            $this->log('env', 'APP_ENV not production', ['value' => $env], 'H1');
            $issues++;
        }

        if ($debug) {
            $this->warn('APP_DEBUG=true — disable on hosting (security risk).');
            $this->log('env', 'APP_DEBUG enabled', ['value' => true], 'H1');
            $issues++;
        }

        $url = config('app.url');
        if (str_contains($url, '.test') || str_contains($url, 'localhost')) {
            $this->warn("APP_URL={$url} — update to production domain before deploy.");
            $this->log('env', 'APP_URL points to local domain', ['value' => $url], 'H1');
            $issues++;
        }

        return $issues;
    }

    private function checkAppKey(): int
    {
        if (empty(config('app.key'))) {
            $this->error('APP_KEY is empty — run php artisan key:generate on hosting.');
            $this->log('env', 'APP_KEY missing', [], 'H1');
            return 1;
        }

        $this->log('env', 'APP_KEY present', ['length' => strlen((string) config('app.key'))], 'H1');
        return 0;
    }

    private function checkStorage(): int
    {
        $issues = 0;
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! is_link($link) && ! is_dir($link)) {
            $this->error('public/storage symlink missing — run php artisan storage:link on hosting.');
            $this->log('storage', 'Symlink missing', ['path' => $link], 'H2');
            $issues++;
        } else {
            $this->log('storage', 'Symlink OK', [
                'link'   => $link,
                'isLink' => is_link($link),
            ], 'H2');
        }

        foreach (['storage', 'storage/app', 'storage/app/public', 'storage/logs', 'storage/framework/cache', 'bootstrap/cache'] as $dir) {
            $path = base_path($dir);
            if (! is_writable($path)) {
                $this->error("Directory not writable: {$dir}");
                $this->log('storage', 'Directory not writable', ['path' => $path], 'H2');
                $issues++;
            }
        }

        return $issues;
    }

    private function checkDatabaseDrivers(): int
    {
        $issues = 0;
        $tables = [
            'sessions' => config('session.driver') === 'database',
            'cache'    => config('cache.default') === 'database',
            'jobs'     => config('queue.default') === 'database',
        ];

        foreach ($tables as $table => $required) {
            if (! $required) {
                continue;
            }

            if (! Schema::hasTable($table)) {
                $this->error("Table `{$table}` missing — required for " . match ($table) {
                    'sessions' => 'SESSION_DRIVER=database',
                    'cache'    => 'CACHE_STORE=database',
                    'jobs'     => 'QUEUE_CONNECTION=database',
                    default    => $table,
                });
                $this->log('db', 'Required table missing', ['table' => $table], 'H3');
                $issues++;
            } else {
                $this->log('db', 'Table exists', ['table' => $table], 'H3');
            }
        }

        try {
            DB::connection()->getPdo();
            Cache::put('deploy_preflight_ping', 'ok', 10);
            $this->log('db', 'DB connection and cache write OK', [], 'H3');
        } catch (\Throwable $e) {
            $this->error('Database/cache error: ' . $e->getMessage());
            $this->log('db', 'Connection failed', ['error' => $e->getMessage()], 'H3');
            $issues++;
        }

        return $issues;
    }

    private function checkMigrations(): int
    {
        Artisan::call('migrate:status');
        $output = Artisan::output();

        if (str_contains($output, 'Pending')) {
            $this->warn('Pending migrations detected — run php artisan migrate on hosting.');
            $this->log('migrate', 'Pending migrations', ['hasPending' => true], 'H3');
            return 1;
        }

        $this->log('migrate', 'All migrations ran', ['hasPending' => false], 'H3');
        return 0;
    }

    private function checkViteBuild(): int
    {
        $manifest = public_path('build/manifest.json');

        if (! file_exists($manifest)) {
            $this->error('Vite build missing — run npm run build before deploy.');
            $this->log('assets', 'Vite manifest missing', ['path' => $manifest], 'H5');
            return 1;
        }

        $this->log('assets', 'Vite build present', [
            'manifest' => $manifest,
            'mtime'    => date('c', filemtime($manifest)),
        ], 'H5');

        return 0;
    }

    private function checkPublicAssets(): int
    {
        $required = [
            'public/favicon.png',
            'public/images/logo-masjidnusa.png',
            'public/images/logo-masjidnusa-icon.png',
            'public/sw.js',
            'public/images/icons/icon-192.png',
        ];

        $missing = [];
        foreach ($required as $file) {
            if (! file_exists(base_path($file))) {
                $missing[] = $file;
            }
        }

        if ($missing) {
            $this->error('Missing public assets: ' . implode(', ', $missing));
            $this->log('assets', 'Public assets missing', ['files' => $missing], 'H5');
            return count($missing);
        }

        $this->log('assets', 'Required public assets present', ['count' => count($required)], 'H5');
        return 0;
    }

    private function checkIntegrations(): int
    {
        $issues = 0;
        $checks = [
            'DUITKU_MERCHANT_CODE' => env('DUITKU_MERCHANT_CODE'),
            'DUITKU_API_KEY'       => env('DUITKU_API_KEY'),
            'VAPID_PUBLIC_KEY'     => config('webpush.vapid.public_key'),
            'VAPID_PRIVATE_KEY'    => config('webpush.vapid.private_key'),
            'GOOGLE_CLIENT_ID'     => config('services.google.client_id'),
            'GOOGLE_CLIENT_SECRET' => config('services.google.client_secret'),
        ];

        foreach ($checks as $name => $value) {
            if (empty($value)) {
                $this->warn("{$name} empty — related feature may not work on hosting.");
                $this->log('integrations', 'Credential empty', ['key' => $name], 'H5');
                $issues++;
            }
        }

        if (config('mail.default') === 'log') {
            $this->warn('MAIL_MAILER=log — emails will not be sent until SMTP is configured.');
            $this->log('integrations', 'Mail driver is log', [], 'H5');
            $issues++;
        }

        $this->log('integrations', 'Google OAuth configured', [
            'enabled' => GoogleAuth::isConfigured(),
        ], 'H5');

        return $issues;
    }

    private function checkUrlGeneration(): int
    {
        $configUrl = rtrim((string) config('app.url'), '/');
        $assetUrl  = asset('images/logo-masjidnusa.png');
        $storageUrl = asset('storage/test.jpg');

        $this->log('url', 'URL generation snapshot (CLI)', [
            'configAppUrl' => $configUrl,
            'assetUrl'     => $assetUrl,
            'storageUrl'   => $storageUrl,
            'filesystemPublicUrl' => config('filesystems.disks.public.url'),
        ], 'H4');

        if (str_starts_with($assetUrl, 'http://') && ! str_contains($configUrl, 'localhost')) {
            $this->warn('Assets use http:// — ensure HTTPS on hosting (TrustProxies + APP_URL).');
            return 1;
        }

        return 0;
    }

    private function checkScheduler(): int
    {
        $events = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->map(fn ($e) => $e->expression . ' → ' . ($e->description ?? $e->command ?? 'event'))
            ->values()
            ->all();

        $this->log('scheduler', 'Scheduled tasks require cron', [
            'cronExample' => '* * * * * cd ' . base_path() . ' && php artisan schedule:run >> /dev/null 2>&1',
            'events'      => $events,
            'queueWorker' => 'php artisan queue:work (required for QUEUE_CONNECTION=database)',
        ], 'H7');

        $this->warn('Hosting needs cron for schedule:run + queue worker for background jobs.');
        return 0;
    }

    private function checkTrustProxies(): int
    {
        $trusted = config('trustedproxy.proxies') ?? env('TRUSTED_PROXIES');
        $this->log('proxy', 'TrustProxies config', [
            'trustedProxies' => $trusted,
            'note'           => 'Behind Cloudflare/cPanel/reverse proxy: set TRUSTED_PROXIES=* or configure in bootstrap/app.php',
        ], 'H4');

        return 0;
    }

    private function log(string $location, string $message, array $data, string $hypothesisId): void
    {
        $payload = json_encode([
            'sessionId'    => 'c5a801',
            'runId'        => 'preflight',
            'hypothesisId' => $hypothesisId,
            'location'     => "DeployPreflightCommand::{$location}",
            'message'      => $message,
            'data'         => $data,
            'timestamp'    => (int) round(microtime(true) * 1000),
        ], JSON_UNESCAPED_SLASHES);

        @file_put_contents(self::LOG_PATH, $payload . PHP_EOL, FILE_APPEND);
    }
}
