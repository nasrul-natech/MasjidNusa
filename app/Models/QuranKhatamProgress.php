<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranKhatamProgress extends Model
{
    protected $table = 'quran_khatam_progress';

    protected $fillable = [
        'program_id', 'surah_number', 'verse_from', 'verse_to',
        'juz_number', 'pages_read', 'read_date',
    ];

    protected function casts(): array
    {
        return [
            'read_date' => 'date',
        ];
    }

    public function program()
    {
        return $this->belongsTo(QuranKhatamProgram::class, 'program_id');
    }
}
