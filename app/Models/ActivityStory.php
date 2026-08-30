<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityStory extends Model
{
    protected $fillable = ['activity_id', 'title', 'media', 'media_type', 'is_highlight', 'sort_order'];

    protected $casts = ['is_highlight' => 'boolean'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function getMediaUrlAttribute(): string
    {
        return asset('storage/' . $this->media);
    }
}
