<?php

namespace Tests\Feature;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DataIntegrityCheckTest extends TestCase
{
    public function createApplication()
    {
        $environment = [
            'APP_ENV' => 'testing',
            'APP_CONFIG_CACHE' => sys_get_temp_dir().'/integrity-config-'.bin2hex(random_bytes(16)).'.php',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_URL' => '',
            'CACHE_STORE' => 'array',
        ];
        $repository = Env::getRepository();
        $original = [];

        foreach ($environment as $key => $value) {
            $original[$key] = $repository->get($key);
            $repository->set($key, $value);
        }

        try {
            $this->assertFileDoesNotExist($environment['APP_CONFIG_CACHE']);
            $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
            // Never read project environment files, including .env.testing.
            $app->useEnvironmentPath(sys_get_temp_dir().'/integrity-env-'.bin2hex(random_bytes(16)));
            $this->traitsUsedByTest = class_uses_recursive(static::class);
            $app->make(Kernel::class)->bootstrap();
            $this->assertSame('sqlite', $app['config']->get('database.default'));
            $this->assertSame(':memory:', $app['config']->get('database.connections.sqlite.database'));

            return $app;
        } finally {
            foreach ($original as $key => $value) {
                $value === null ? $repository->clear($key) : $repository->set($key, $value);
            }
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->nullable();
        });
        Schema::create('quran_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('surah_id');
            $table->unsignedInteger('verse_number');
            $table->boolean('is_last_read');
        });
        Schema::create('quran_khatam_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->date('read_date');
            $table->unsignedInteger('surah_number');
            $table->unsignedInteger('verse_from');
            $table->unsignedInteger('verse_to');
        });
        Schema::create('prayer_locations', function (Blueprint $table) {
            $table->id();
            $table->string('equran_province')->nullable();
            $table->string('equran_city')->nullable();
        });
    }

    public function test_clean_data_succeeds_without_changing_rows(): void
    {
        DB::table('payment_proofs')->insert([
            ['transaction_id' => 1], ['transaction_id' => 2],
            ['transaction_id' => null], ['transaction_id' => null],
        ]);
        DB::table('quran_bookmarks')->insert([
            ['user_id' => 1, 'surah_id' => 1, 'verse_number' => 1, 'is_last_read' => false],
            ['user_id' => 1, 'surah_id' => 1, 'verse_number' => 1, 'is_last_read' => true],
            ['user_id' => 2, 'surah_id' => 1, 'verse_number' => 1, 'is_last_read' => true],
            ['user_id' => 2, 'surah_id' => 1, 'verse_number' => 1, 'is_last_read' => false],
        ]);
        $progress = ['program_id' => 1, 'read_date' => '2026-01-01', 'surah_number' => 1, 'verse_from' => 1, 'verse_to' => 3];
        DB::table('quran_khatam_progress')->insert([
            $progress,
            array_replace($progress, ['program_id' => 2]),
            array_replace($progress, ['read_date' => '2026-01-02']),
            array_replace($progress, ['surah_number' => 2]),
            array_replace($progress, ['verse_from' => 2]),
            array_replace($progress, ['verse_to' => 4]),
        ]);
        DB::table('prayer_locations')->insert([
            ['equran_province' => 'private-province', 'equran_city' => 'private-city'],
            ['equran_province' => 'other-province', 'equran_city' => 'private-city'],
            ['equran_province' => 'private-province', 'equran_city' => 'other-city'],
            ['equran_province' => null, 'equran_city' => 'private-city'],
            ['equran_province' => null, 'equran_city' => 'private-city'],
            ['equran_province' => 'private-province', 'equran_city' => null],
            ['equran_province' => 'private-province', 'equran_city' => null],
        ]);

        $this->assertCheck(0, [0, 0, 0, 0, 0]);
    }

    public function test_conflicts_fail_without_exposing_or_changing_rows(): void
    {
        DB::table('payment_proofs')->insert(array_fill(0, 3, ['transaction_id' => 987654]));
        DB::table('payment_proofs')->insert(array_fill(0, 2, ['transaction_id' => 987655]));
        DB::table('quran_bookmarks')->insert(array_fill(0, 3, [
            'user_id' => 987654, 'surah_id' => 1, 'verse_number' => 1, 'is_last_read' => false,
        ]));
        DB::table('quran_bookmarks')->insert([
            ['user_id' => 987654, 'surah_id' => 1, 'verse_number' => 1, 'is_last_read' => true],
            ['user_id' => 987654, 'surah_id' => 2, 'verse_number' => 2, 'is_last_read' => true],
        ]);
        DB::table('quran_khatam_progress')->insert(array_fill(0, 3, [
            'program_id' => 987654, 'read_date' => '2026-01-01', 'surah_number' => 1, 'verse_from' => 1, 'verse_to' => 3,
        ]));
        DB::table('prayer_locations')->insert(array_fill(0, 3, [
            'equran_province' => 'private-province', 'equran_city' => 'private-city',
        ]));

        $this->assertCheck(1, [2, 1, 1, 1, 1]);
    }

    private function assertCheck(int $exitCode, array $counts): void
    {
        $labels = [
            'payment_proofs.transaction_id',
            'quran_bookmarks.non_last_read',
            'quran_bookmarks.last_read_per_user',
            'quran_khatam_progress.read_range',
            'prayer_locations.equran_province_equran_city',
        ];
        $tables = ['payment_proofs', 'quran_bookmarks', 'quran_khatam_progress', 'prayer_locations'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }

        DB::enableQueryLog();
        $this->assertSame($exitCode, Artisan::call('system:check-data-integrity'));
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $expected = '';
        foreach ($labels as $index => $label) {
            $expected .= $label.': '.$counts[$index].PHP_EOL;
        }
        $this->assertSame($expected, Artisan::output());
        $this->assertCount(5, $queries);
        foreach ($queries as $query) {
            $this->assertMatchesRegularExpression('/^select\b/i', $query['query']);
        }
        foreach ($tables as $table) {
            $this->assertSame($before[$table], DB::table($table)->orderBy('id')->get()->toJson());
        }
    }
}
