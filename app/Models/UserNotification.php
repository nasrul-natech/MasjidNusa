<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id', 'title', 'body', 'category', 'link', 'icon', 'is_read', 'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    const CATEGORY_PAYMENT = 'payment';
    const CATEGORY_ACTIVITY = 'activity';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_SYSTEM = 'system';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'payment' => '💰',
            'activity' => '📅',
            'security' => '🔒',
            default => '🔔',
        };
    }
}
