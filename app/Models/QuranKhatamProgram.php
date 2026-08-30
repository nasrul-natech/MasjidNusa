<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranKhatamProgram extends Model
{
    protected $fillable = [
        'user_id', 'title', 'target_days', 'start_date', 'target_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function progress()
    {
        return $this->hasMany(QuranKhatamProgress::class, 'program_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getProgressPercentAttribute(): float
    {
        $totalPages = 604;
        $pagesRead = $this->progress()->sum('pages_read');
        return min(100, round(($pagesRead / $totalPages) * 100, 1));
    }

    public function getTotalPagesReadAttribute(): int
    {
        return (int) $this->progress()->sum('pages_read');
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, (int) now()->diffInDays($this->target_date, false));
    }

    public function getDaysElapsedAttribute(): int
    {
        return (int) $this->start_date->diffInDays(now());
    }
}
