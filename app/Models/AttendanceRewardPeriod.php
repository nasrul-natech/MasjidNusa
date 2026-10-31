<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class AttendanceRewardPeriod extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'start_date' => 'immutable_date', 'end_date' => 'immutable_date',
            'starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime',
            'effective_cutoff_at' => 'immutable_datetime', 'finalized_at' => 'immutable_datetime',
            'winner_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $period) {
            if ($period->getOriginal('status') === 'final') {
                throw new LogicException('Hasil periode final tidak dapat diubah.');
            }
            if ($period->getOriginal('status') === 'active' && $period->isDirty([
                'name', 'start_date', 'end_date', 'timezone', 'starts_at', 'ends_at',
                'winner_count', 'rules_version', 'created_by',
            ])) {
                throw new LogicException('Aturan periode aktif tidak dapat diubah.');
            }
        });
        static::deleting(fn () => throw new LogicException('Riwayat periode hadiah tidak boleh dihapus.'));
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(AttendanceRewardRecipient::class, 'period_id')->orderBy('rank');
    }
}
