<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityGallery extends Model
{
    protected $fillable = ['activity_id', 'image', 'caption', 'sort_order'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }
}
