<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AttendanceRewardRecipient extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'rank' => 'integer', 'points' => 'integer', 'reached_at' => 'immutable_datetime',
            'status_changed_at' => 'immutable_datetime', 'delivered_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $recipient) {
            if ($recipient->isDirty(['period_id', 'user_id', 'rank', 'display_name', 'points', 'reached_at'])) {
                throw new LogicException('Snapshot penerima tidak dapat diubah.');
            }
        });
        static::deleting(fn () => throw new LogicException('Snapshot penerima tidak boleh dihapus.'));
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AttendanceRewardPeriod::class, 'period_id');
    }
}
