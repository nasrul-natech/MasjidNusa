<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopupAd extends Model
{
    protected $fillable = [
        'title', 'ad_type', 'image', 'link', 'content', 'activity_id',
        'is_active', 'start_date', 'end_date', 'duration', 'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'duration' => 'integer',
        'priority' => 'integer',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', today());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', today());
            })
            ->orderBy('priority')
            ->orderByDesc('created_at');
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) return false;
        if ($this->start_date && $this->start_date->startOfDay()->isFuture()) return false;
        if ($this->end_date && $this->end_date->endOfDay()->isPast()) return false;
        return true;
    }
}
