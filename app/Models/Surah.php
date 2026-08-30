<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surah extends Model
{
    protected $fillable = [
        'number', 'name_arabic', 'name_latin', 'name_translation',
        'revelation_type', 'total_verses', 'juz_start',
        'description_id', 'description_en',
    ];

    public function verses()
    {
        return $this->hasMany(QuranVerse::class)->orderBy('verse_number');
    }

    public function scopeByNumber($query, int $number)
    {
        return $query->where('number', $number);
    }

    public function getIsMeccanAttribute(): bool
    {
        return $this->revelation_type === 'meccan';
    }
}
