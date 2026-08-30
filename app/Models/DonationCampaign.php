<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationCampaign extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'content', 'target_amount', 'collected_amount',
        'category_id', 'banner', 'status', 'start_date', 'end_date', 'is_featured',
    ];

    protected $casts = [
        'target_amount' => 'integer',
        'collected_amount' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_featured' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(DonationCategory::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class, 'campaign_id');
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->target_amount <= 0) return 0;
        return min(100, (int) (($this->collected_amount / $this->target_amount) * 100));
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner ? asset('storage/' . $this->banner) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
