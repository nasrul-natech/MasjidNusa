<?php

namespace App\Console\Commands;

use App\Models\Surah;
use App\Models\QuranVerse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeedQuranData extends Command
{
    protected $signature = 'quran:seed
                            {--surah= : Seed specific surah number (1-114)}
                            {--fresh : Drop existing data before seeding}
                            {--transliteration : Re-seed transliteration data only}';

    protected $description = 'Seed Al-Quran data from Quran.com API v4 (surahs + verses with tajweed, translations, transliteration)';

    private const API_BASE = 'https://api.quran.com/api/v4';
    private const AUDIO_BASE = 'https://cdn.islamic.network/quran/audio/128/ar.alafasy';
    private const TIMEOUT = 30;
    private const TRANSLATION_ID = 33;  // Kemenag Indonesia
    private const TRANSLATION_EN = 131; // Sahih International

    private int $absoluteVerseCounter = 0;

    public function handle(): int
    {
        if ($this->option('transliteration')) {
            return $this->reseedTransliterations();
        }

        if ($this->option('fresh')) {
            $this->warn('Menghapus data Al-Quran yang ada...');
            QuranVerse::truncate();
            Surah::truncate();
        }

        $specificSurah = $this->option('surah');

        if ($specificSurah) {
            return $this->seedSingleSurah((int) $specificSurah);
        }

        return $this->seedAll();
    }

    private function reseedTransliterations(): int
    {
        $surahs = Surah::orderBy('number')->get();
        $this->info('Re-seeding transliteration for ' . $surahs->count() . ' surahs...');

        $bar = $this->output->createProgressBar($surahs->count());
        $bar->start();

        $totalUpdated = 0;

        foreach ($surahs as $surah) {
            $transliterations = $this->fetchTransliterations($surah->number);

            foreach ($transliterations as $verseNum => $text) {
                $updated = QuranVerse::where('surah_id', $surah->id)
                    ->where('verse_number', $verseNum)
                    ->update(['text_transliteration' => $text]);
                $totalUpdated += $updated;
            }

            $bar->advance();
            usleep(150000);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Selesai! {$totalUpdated} ayat diupdate dengan transliterasi.");

        return self::SUCCESS;
    }

    private function seedAll(): int
    {
        $this->info('Mengambil daftar surah dari Quran.com API...');

        $chaptersResponse = Http::timeout(self::TIMEOUT)
            ->get(self::API_BASE . '/chapters', ['language' => 'id']);

        if (!$chaptersResponse->successful()) {
            $this->error('Gagal mengambil daftar surah: HTTP ' . $chaptersResponse->status());
            return self::FAILURE;
        }

        $chapters = $chaptersResponse->json('chapters', []);
        $this->info('Ditemukan ' . count($chapters) . ' surah. Mulai seeding...');

        $bar = $this->output->createProgressBar(count($chapters));
        $bar->start();

        $this->absoluteVerseCounter = 0;
        $failed = 0;

        foreach ($chapters as $chapter) {
            try {
                $this->processSurah($chapter);
            } catch (\Throwable $e) {
                $failed++;
                Log::error("Quran seed error surah {$chapter['id']}: " . $e->getMessage());
                $this->newLine();
                $this->error("  Gagal surah {$chapter['id']} ({$chapter['name_simple']}): " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $totalVerses = QuranVerse::count();
        $totalSurahs = Surah::count();
        $this->info("Selesai! {$totalSurahs} surah, {$totalVerses} ayat tersimpan. Gagal: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function seedSingleSurah(int $number): int
    {
        $this->info("Mengambil data surah {$number}...");

        $chapterResponse = Http::timeout(self::TIMEOUT)
            ->get(self::API_BASE . "/chapters/{$number}", ['language' => 'id']);

        if (!$chapterResponse->successful()) {
            $this->error('Gagal mengambil surah: HTTP ' . $chapterResponse->status());
            return self::FAILURE;
        }

        $this->absoluteVerseCounter = $this->calculateAbsoluteStart($number);

        try {
            $this->processSurah($chapterResponse->json('chapter'));
            $this->info('Surah ' . $number . ' berhasil diseed.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function processSurah(array $chapter): void
    {
        $surah = Surah::updateOrCreate(
            ['number' => $chapter['id']],
            [
                'name_arabic' => $chapter['name_arabic'],
                'name_latin' => $chapter['name_simple'],
                'name_translation' => $chapter['translated_name']['name'] ?? null,
                'revelation_type' => $chapter['revelation_place'] === 'makkah' ? 'meccan' : 'medinan',
                'total_verses' => $chapter['verses_count'],
                'juz_start' => 1,
            ]
        );

        $page = 1;
        $allVerses = [];

        while (true) {
            $versesResponse = Http::timeout(self::TIMEOUT)->get(
                self::API_BASE . "/verses/by_chapter/{$chapter['id']}",
                [
                    'language' => 'id',
                    'translations' => self::TRANSLATION_ID . ',' . self::TRANSLATION_EN,
                    'fields' => 'text_uthmani,text_imlaei',
                    'translation_fields' => 'text',
                    'per_page' => 50,
                    'page' => $page,
                ]
            );

            if (!$versesResponse->successful()) {
                throw new \RuntimeException("Gagal fetch ayat surah {$chapter['id']} halaman {$page}");
            }

            $data = $versesResponse->json();
            $verses = $data['verses'] ?? [];

            if (empty($verses)) {
                break;
            }

            $allVerses = array_merge($allVerses, $verses);

            $pagination = $data['pagination'] ?? [];
            if ($page >= ($pagination['total_pages'] ?? 1)) {
                break;
            }
            $page++;
        }

        $tajweedTexts = $this->fetchTajweedTexts($chapter['id']);
        $transliterations = $this->fetchTransliterations($chapter['id']);

        DB::beginTransaction();
        try {
            foreach ($allVerses as $verse) {
                $verseNum = $verse['verse_number'] ?? ($verse['verse_key'] ? (int) explode(':', $verse['verse_key'])[1] : 0);
                $this->absoluteVerseCounter++;

                $translationId = '';
                $translationEn = '';
                foreach (($verse['translations'] ?? []) as $t) {
                    $text = strip_tags($t['text'] ?? '');
                    if (($t['resource_id'] ?? 0) == self::TRANSLATION_ID) {
                        $translationId = $text;
                    } elseif (($t['resource_id'] ?? 0) == self::TRANSLATION_EN) {
                        $translationEn = $text;
                    }
                }

                $juzNumber = $this->getJuzNumber($chapter['id'], $verseNum);
                $pageNumber = $verse['page_number'] ?? 0;

                QuranVerse::updateOrCreate(
                    ['surah_id' => $surah->id, 'verse_number' => $verseNum],
                    [
                        'juz_number' => $juzNumber,
                        'page_number' => $pageNumber,
                        'text_uthmani' => $verse['text_uthmani'] ?? $verse['text_imlaei'] ?? '',
                        'text_tajweed' => $tajweedTexts[$verseNum] ?? null,
                        'text_transliteration' => $transliterations[$verseNum] ?? null,
                        'translation_id' => $translationId,
                        'translation_en' => $translationEn,
                        'audio_url' => self::AUDIO_BASE . '/' . $this->absoluteVerseCounter . '.mp3',
                    ]
                );
            }

            if ($allVerses) {
                $firstJuz = $this->getJuzNumber($chapter['id'], 1);
                $surah->update(['juz_start' => $firstJuz]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function fetchTajweedTexts(int $chapterId): array
    {
        $texts = [];
        $page = 1;

        while (true) {
            try {
                $response = Http::timeout(self::TIMEOUT)->get(
                    self::API_BASE . '/quran/verses/uthmani_tajweed',
                    ['chapter_number' => $chapterId, 'per_page' => 50, 'page' => $page]
                );

                if (!$response->successful()) {
                    break;
                }

                $data = $response->json();
                foreach (($data['verses'] ?? []) as $v) {
                    $verseNum = (int) explode(':', $v['verse_key'])[1];
                    $texts[$verseNum] = $v['text_uthmani_tajweed'] ?? null;
                }

                if ($page >= ($data['pagination']['total_pages'] ?? 1)) {
                    break;
                }
                $page++;
            } catch (\Throwable $e) {
                Log::warning("Tajweed fetch failed for surah {$chapterId} page {$page}: " . $e->getMessage());
                break;
            }
        }

        return $texts;
    }

    private function fetchTransliterations(int $chapterId): array
    {
        $texts = [];

        try {
            $response = Http::timeout(self::TIMEOUT)->get(
                self::API_BASE . '/quran/translations/57',
                ['chapter_number' => $chapterId]
            );

            if (!$response->successful()) {
                return $texts;
            }

            $data = $response->json();
            foreach (($data['translations'] ?? []) as $index => $t) {
                $verseNum = $index + 1;
                $text = strip_tags($t['text'] ?? '');
                if ($text) {
                    $texts[$verseNum] = $text;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Transliteration fetch failed for surah {$chapterId}: " . $e->getMessage());
        }

        return $texts;
    }

    private function calculateAbsoluteStart(int $surahNumber): int
    {
        $verseCounts = [
            7,286,200,176,120,165,206,75,129,109,123,111,43,52,99,128,111,
            110,98,135,112,78,118,64,77,227,93,88,69,60,34,30,73,54,45,83,
            182,88,75,85,54,53,89,59,37,35,38,88,52,77,30,37,49,29,61,
            40,46,42,29,19,36,25,22,17,19,26,30,20,15,21,11,8,18,12,31,
            18,34,39,36,20,56,54,22,45,52,11,13,46,11,176,40,46,35,206,
            154,73,55,63,53,52,165,99,54,28,18,10,27,
            5,8,19,5,8,8,11,11,8,3,9,5,4,7,3,6,3,5,4,5,6
        ];

        $count = 0;
        for ($i = 0; $i < $surahNumber - 1; $i++) {
            $count += $verseCounts[$i] ?? 0;
        }
        return $count;
    }

    private function getJuzNumber(int $surahNumber, int $verseNumber): int
    {
        $juzBoundaries = [
            [1, 1], [2, 142], [2, 253], [3, 93], [4, 24], [4, 148], [5, 82],
            [6, 111], [7, 88], [8, 41], [9, 93], [11, 6], [12, 53], [15, 1],
            [17, 1], [18, 75], [21, 1], [23, 1], [25, 21], [27, 56], [29, 46],
            [33, 31], [36, 28], [39, 32], [41, 47], [46, 1], [51, 31], [58, 1],
            [67, 1], [78, 1],
        ];

        $juz = 1;
        for ($i = count($juzBoundaries) - 1; $i >= 0; $i--) {
            $bSurah = $juzBoundaries[$i][0];
            $bVerse = $juzBoundaries[$i][1];
            if ($surahNumber > $bSurah || ($surahNumber === $bSurah && $verseNumber >= $bVerse)) {
                $juz = $i + 1;
                break;
            }
        }

        return $juz;
    }
}
