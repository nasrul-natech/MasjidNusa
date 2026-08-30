<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MosqueProfile extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'email', 'description',
        'vision', 'mission', 'photo', 'banner',
        'latitude', 'longitude', 'google_maps_url', 'website', 'social_media',
    ];

    protected $casts = [
        'social_media' => 'array',
    ];

    public function histories()
    {
        return $this->hasMany(MosqueHistory::class)->orderBy('sort_order');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner ? asset('storage/' . $this->banner) : null;
    }

    public static function current(): ?self
    {
        return self::first();
    }
}
