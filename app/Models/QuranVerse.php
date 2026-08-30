<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranVerse extends Model
{
    protected $fillable = [
        'surah_id', 'verse_number', 'juz_number', 'page_number',
        'text_uthmani', 'text_tajweed', 'text_transliteration',
        'translation_id', 'translation_en', 'audio_url',
    ];

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }

    public function scopeByJuz($query, int $juz)
    {
        return $query->where('juz_number', $juz);
    }

    public function scopeBySurah($query, int $surahId)
    {
        return $query->where('surah_id', $surahId);
    }

    public function getVerseKeyAttribute(): string
    {
        return $this->surah_id . ':' . $this->verse_number;
    }
}
