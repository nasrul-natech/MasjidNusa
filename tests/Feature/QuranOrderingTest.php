<?php

namespace Tests\Feature;

use App\Models\QuranVerse;
use App\Models\Surah;
use App\Services\QuranService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QuranOrderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        foreach ([90 => 1, 10 => 2, 50 => 3] as $id => $number) {
            $surah = new Surah;
            $surah->forceFill([
                'id' => $id, 'number' => $number, 'name_arabic' => 'Surah '.$number,
                'name_latin' => 'Surah '.$number, 'revelation_type' => 'meccan', 'total_verses' => 2,
            ])->save();

            foreach ([2, 1] as $verseNumber) {
                QuranVerse::create([
                    'surah_id' => $id, 'verse_number' => $verseNumber,
                    'juz_number' => $number === 3 ? 2 : 1,
                    'text_uthmani' => 'Text', 'translation_id' => 'matching translation',
                    'translation_en' => 'matching translation', 'text_transliteration' => 'transliterated',
                ]);
            }
        }
    }

    public function test_juz_verses_follow_surah_numbers_then_verse_numbers(): void
    {
        $verses = app(QuranService::class)->getJuzVerses(1);

        $this->assertSame(['1:1', '1:2', '2:1', '2:2'], $verses
            ->map(fn ($verse) => $verse->surah->number.':'.$verse->verse_number)->all());
        $this->assertSame([90, 90, 10, 10], $verses->pluck('surah_id')->all());
    }

    public function test_search_orders_before_limiting_in_both_languages_and_transliteration(): void
    {
        foreach ([['matching', 'id'], ['matching', 'en'], ['transliterated', 'id']] as [$query, $lang]) {
            $verses = app(QuranService::class)->search($query, $lang, 3);

            $this->assertSame(['1:1', '1:2', '2:1'], $verses
                ->map(fn ($verse) => $verse->surah->number.':'.$verse->verse_number)->all());
        }
    }

    public function test_juz_info_uses_canonical_surah_order_and_preserves_counts_when_cached(): void
    {
        $service = app(QuranService::class);

        foreach ([$service->getJuzInfo(), $service->getJuzInfo()] as $info) {
            $this->assertSame([1, 2], $info->pluck('number')->all());
            $this->assertSame([1, 2], $info[0]['surahs']->pluck('number')->all());
            $this->assertSame([90, 10], $info[0]['surahs']->pluck('id')->all());
            $this->assertSame([3], $info[1]['surahs']->pluck('number')->all());
            $this->assertSame([4, 2], $info->pluck('total_verses')->all());
        }
    }
}
