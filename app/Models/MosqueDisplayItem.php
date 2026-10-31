<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MosqueDisplayItem extends Model
{
    protected $fillable = ['mosque_display_id', 'type', 'title', 'photo', 'youtube_id', 'sort_order', 'duration_seconds', 'starts_at', 'ends_at', 'is_active'];

    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean'];
}
