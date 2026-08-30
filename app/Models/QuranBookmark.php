<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranBookmark extends Model
{
    protected $fillable = [
        'user_id', 'surah_id', 'verse_number', 'label', 'is_last_read',
    ];

    protected function casts(): array
    {
        return [
            'is_last_read' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }
}
