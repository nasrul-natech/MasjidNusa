<?php

namespace App\Services;

use App\Models\QuranVerse;
use App\Models\Surah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class QuranService
{
    private const CACHE_TTL = 86400;

    public function __construct(private readonly QuranApiService $apiService)
    {
    }

    public function isLocalDataAvailable(): bool
    {
        return Surah::exists();
    }

    public function getSurahList(): Collection
    {
        if (!$this->isLocalDataAvailable()) {
            return $this->apiService->getSurahList();
        }

        $list = Cache::remember('quran_surah_list', self::CACHE_TTL, function () {
            return Surah::orderBy('number')->get();
        });

        return $list->isNotEmpty() ? $list : $this->apiService->getSurahList();
    }

    public function getSurah(int $number): ?object
    {
        if (!$this->isLocalDataAvailable()) {
            return $this->apiService->getSurah($number);
        }

        return Cache::remember("quran_surah_{$number}", self::CACHE_TTL, function () use ($number) {
            return Surah::where('number', $number)->first();
        });
    }

    public function getAllSurahVerses(int $surahId): Collection
    {
        if (!$this->isLocalDataAvailable()) {
            return $this->apiService->getSurahVerses($surahId);
        }

        return QuranVerse::where('surah_id', $surahId)
            ->orderBy('verse_number')
            ->get();
    }

    public function getJuzVerses(int $juzNumber): Collection
    {
        if (!$this->isLocalDataAvailable()) {
            return $this->apiService->getJuzVerses($juzNumber);
        }

        return QuranVerse::where('juz_number', $juzNumber)
            ->with('surah')
            ->orderBy('surah_id')
            ->orderBy('verse_number')
            ->get();
    }

    public function getJuzInfo(): Collection
    {
        if (!$this->isLocalDataAvailable()) {
            return $this->apiService->getJuzInfo();
        }

        return Cache::remember('quran_juz_info', self::CACHE_TTL, function () {
            $data = QuranVerse::selectRaw('juz_number, surah_id, count(*) as verse_count')
                ->groupBy('juz_number', 'surah_id')
                ->orderBy('juz_number')
                ->orderBy('surah_id')
                ->get();

            $surahIds = $data->pluck('surah_id')->unique();
            $surahs = Surah::whereIn('id', $surahIds)
                ->get(['id', 'number', 'name_arabic', 'name_latin'])
                ->keyBy('id');

            return $data->groupBy('juz_number')->map(function ($rows, $juzNum) use ($surahs) {
                $juzSurahs = $rows->map(fn($r) => $surahs->get($r->surah_id))->filter()->unique('id')->values();
                return [
                    'number' => $juzNum,
                    'surahs' => $juzSurahs,
                    'total_verses' => $rows->sum('verse_count'),
                ];
            })->values();
        });
    }

    public function getVerse(int $surahNumber, int $verseNumber): ?object
    {
        if (!$this->isLocalDataAvailable()) {
            return $this->apiService->getSurahVerses($surahNumber)
                ->firstWhere('verse_number', $verseNumber);
        }

        return QuranVerse::whereHas('surah', fn($q) => $q->where('number', $surahNumber))
            ->where('verse_number', $verseNumber)
            ->with('surah')
            ->first();
    }

    public function search(string $query, string $lang = 'id', int $limit = 30): Collection
    {
        if (!$this->isLocalDataAvailable()) {
            return $this->apiService->search($query, $lang, $limit);
        }

        $field = $lang === 'en' ? 'translation_en' : 'translation_id';

        return QuranVerse::with('surah')
            ->where(function ($q) use ($query, $field) {
                $q->where($field, 'like', "%{$query}%")
                  ->orWhere('text_transliteration', 'like', "%{$query}%");
            })
            ->orderBy('surah_id')
            ->orderBy('verse_number')
            ->limit($limit)
            ->get();
    }
}
