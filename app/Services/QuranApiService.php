<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuranApiService
{
    private const API_BASE = 'https://api.quran.com/api/v4';
    private const AUDIO_BASE = 'https://cdn.islamic.network/quran/audio/128/ar.alafasy';
    private const TIMEOUT = 30;
    private const TRANSLATION_ID = 33;
    private const TRANSLATION_EN = 131;
    private const CACHE_TTL = 3600;

    public function getSurahList(): Collection
    {
        return Cache::remember('quran_api_surah_list', self::CACHE_TTL, function () {
            $response = Http::timeout(self::TIMEOUT)
                ->get(self::API_BASE . '/chapters', ['language' => 'id']);

            if (!$response->successful()) {
                Log::error('Quran API: failed to fetch chapters', ['status' => $response->status()]);
                return collect();
            }

            return collect($response->json('chapters', []))->map(function (array $chapter) {
                return (object) [
                    'id' => $chapter['id'],
                    'number' => $chapter['id'],
                    'name_arabic' => $chapter['name_arabic'],
                    'name_latin' => $chapter['name_simple'],
                    'name_translation' => $chapter['translated_name']['name'] ?? '',
                    'revelation_type' => ($chapter['revelation_place'] ?? '') === 'makkah' ? 'meccan' : 'medinan',
                    'total_verses' => $chapter['verses_count'],
                ];
            });
        });
    }

    public function getSurah(int $number): ?object
    {
        return $this->getSurahList()->firstWhere('number', $number);
    }

    public function getSurahVerses(int $surahNumber): Collection
    {
        return Cache::remember("quran_api_verses_{$surahNumber}", self::CACHE_TTL, function () use ($surahNumber) {
            $surah = $this->getSurah($surahNumber);
            if (!$surah) {
                return collect();
            }

            $page = 1;
            $allVerses = [];

            while (true) {
                $response = Http::timeout(self::TIMEOUT)->get(
                    self::API_BASE . "/verses/by_chapter/{$surahNumber}",
                    [
                        'language' => 'id',
                        'translations' => self::TRANSLATION_ID . ',' . self::TRANSLATION_EN,
                        'fields' => 'text_uthmani,text_imlaei',
                        'translation_fields' => 'text',
                        'per_page' => 50,
                        'page' => $page,
                    ]
                );

                if (!$response->successful()) {
                    Log::error('Quran API: failed to fetch verses', [
                        'surah' => $surahNumber,
                        'page' => $page,
                        'status' => $response->status(),
                    ]);
                    break;
                }

                $data = $response->json();
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

            return collect($allVerses)->map(function (array $verse) use ($surah) {
                $verseNum = $verse['verse_number']
                    ?? (isset($verse['verse_key']) ? (int) explode(':', $verse['verse_key'])[1] : 0);

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

                $surahObj = (object) [
                    'id' => $surah->id,
                    'number' => $surah->number,
                    'name_latin' => $surah->name_latin,
                    'name_arabic' => $surah->name_arabic,
                ];

                return (object) [
                    'surah_id' => $surah->id,
                    'verse_number' => $verseNum,
                    'juz_number' => $verse['juz_number'] ?? 1,
                    'page_number' => $verse['page_number'] ?? 0,
                    'text_uthmani' => $verse['text_uthmani'] ?? $verse['text_imlaei'] ?? '',
                    'text_tajweed' => null,
                    'text_transliteration' => null,
                    'translation_id' => $translationId,
                    'translation_en' => $translationEn,
                    'audio_url' => self::AUDIO_BASE . '/' . ($verse['id'] ?? 0) . '.mp3',
                    'surah' => $surahObj,
                ];
            });
        });
    }

    public function getJuzVerses(int $juzNumber): Collection
    {
        return Cache::remember("quran_api_juz_{$juzNumber}", self::CACHE_TTL, function () use ($juzNumber) {
            $response = Http::timeout(self::TIMEOUT)->get(
                self::API_BASE . "/verses/by_juz/{$juzNumber}",
                [
                    'language' => 'id',
                    'translations' => self::TRANSLATION_ID,
                    'fields' => 'text_uthmani,text_imlaei',
                    'translation_fields' => 'text',
                    'per_page' => 300,
                ]
            );

            if (!$response->successful()) {
                return collect();
            }

            $surahMap = $this->getSurahList()->keyBy('number');

            return collect($response->json('verses', []))->map(function (array $verse) use ($surahMap) {
                $surahNum = isset($verse['verse_key'])
                    ? (int) explode(':', $verse['verse_key'])[0]
                    : 0;
                $verseNum = $verse['verse_number']
                    ?? (isset($verse['verse_key']) ? (int) explode(':', $verse['verse_key'])[1] : 0);

                $surahMeta = $surahMap->get($surahNum);
                $surahObj = $surahMeta ? (object) [
                    'id' => $surahMeta->id,
                    'number' => $surahMeta->number,
                    'name_latin' => $surahMeta->name_latin,
                    'name_arabic' => $surahMeta->name_arabic,
                ] : null;

                $translationId = '';
                foreach (($verse['translations'] ?? []) as $t) {
                    if (($t['resource_id'] ?? 0) == self::TRANSLATION_ID) {
                        $translationId = strip_tags($t['text'] ?? '');
                    }
                }

                return (object) [
                    'surah_id' => $surahObj->id ?? $surahNum,
                    'verse_number' => $verseNum,
                    'juz_number' => $juzNumber,
                    'page_number' => $verse['page_number'] ?? 0,
                    'text_uthmani' => $verse['text_uthmani'] ?? $verse['text_imlaei'] ?? '',
                    'text_tajweed' => null,
                    'text_transliteration' => null,
                    'translation_id' => $translationId,
                    'translation_en' => '',
                    'audio_url' => self::AUDIO_BASE . '/' . ($verse['id'] ?? 0) . '.mp3',
                    'surah' => $surahObj,
                ];
            });
        });
    }

    public function getJuzInfo(): Collection
    {
        return collect(range(1, 30))->map(fn ($j) => [
            'number' => $j,
            'surahs' => collect(),
            'total_verses' => 0,
        ]);
    }

    public function search(string $query, string $lang = 'id', int $limit = 30): Collection
    {
        $results = collect();

        foreach ($this->getSurahList() as $surah) {
            if ($results->count() >= $limit) {
                break;
            }

            $verses = $this->getSurahVerses($surah->number);
            $field = $lang === 'en' ? 'translation_en' : 'translation_id';

            foreach ($verses as $verse) {
                if ($results->count() >= $limit) {
                    break 2;
                }

                $text = $verse->{$field} ?? '';
                $translit = $verse->text_transliteration ?? '';

                if (stripos($text, $query) !== false || stripos($translit, $query) !== false) {
                    $results->push($verse);
                }
            }
        }

        return $results;
    }
}
