<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Activity extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'content', 'category_id', 'location',
        'start_date', 'end_date', 'banner', 'status', 'is_featured', 'show_popup', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_featured' => 'boolean',
        'show_popup' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ActivityCategory::class);
    }

    public function attendanceSetting()
    {
        return $this->hasOne(ActivityAttendanceSetting::class);
    }

    public function attendanceSession()
    {
        return $this->hasOne(ActivityAttendanceSession::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function galleries()
    {
        return $this->hasMany(ActivityGallery::class)->orderBy('sort_order');
    }

    public function stories()
    {
        return $this->hasMany(ActivityStory::class)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'publish');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>=', now())->orderBy('start_date');
    }

    public function scopeCurrentAgenda(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('start_date', '>=', now())
                ->orWhere(function (Builder $query) {
                    $query->where('status', 'publish')->where('start_date', '<=', now())
                        ->whereHas('attendanceSetting', fn ($settings) => $settings->where('enabled', true))
                        ->whereDoesntHave('attendanceSession');
                })
                ->orWhereHas('attendanceSession', fn ($session) => $session->where('closes_at', '>', now()));
        });
    }

    public function getAttendancePhaseAttribute(): string
    {
        if ($this->status !== 'publish' || ! $this->attendanceSetting?->enabled) {
            return 'disabled';
        }
        if ($session = $this->attendanceSession) {
            return now()->lt($session->closes_at) ? 'checkin' : 'closed';
        }

        return now()->gte($this->start_date) ? 'ongoing' : 'upcoming';
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereIn('status', ['selesai', 'arsip'])
            ->orWhere('end_date', '<', now())
            ->orderByDesc('start_date');
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->banner ? asset('storage/' . $this->banner) : null;
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_date > now();
    }
}
