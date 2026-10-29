<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Donation extends Model
{
    protected $fillable = [
        'donation_code', 'user_id', 'campaign_id', 'amount',
        'donor_name', 'donor_email', 'donor_phone', 'message',
        'is_anonymous', 'status', 'payment_method_type',
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_anonymous' => 'boolean',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_WAITING_VERIFICATION = 'waiting_verification';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(DonationCampaign::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function latestTransaction()
    {
        return $this->hasOne(PaymentTransaction::class)->latestOfMany();
    }

    public function isSettled(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_VERIFIED], true);
    }

    /**
     * Pindah ke status lunas sekali saja, dari status asal yang diizinkan, lalu naikkan saldo.
     * Donasi yang sudah lunas atau statusnya sudah berubah tidak menambah saldo lagi.
     */
    public function settle(string $status, array $fromStatuses, ?callable $beforeCommit = null): bool
    {
        return $this->transition($status, $fromStatuses, true, $beforeCommit);
    }

    /**
     * Tutup donasi yang belum lunas. Status di luar daftar asal tidak ditimpa dan saldo tidak dikurangi.
     */
    public function closeIfOpen(string $status, array $fromStatuses, ?callable $beforeCommit = null): bool
    {
        return $this->transition($status, $fromStatuses, false, $beforeCommit);
    }

    private function transition(string $status, array $fromStatuses, bool $collect, ?callable $beforeCommit): bool
    {
        $changed = false;

        DB::transaction(function () use ($status, $fromStatuses, $collect, $beforeCommit, &$changed) {
            $locked = static::query()->whereKey($this->getKey())->lockForUpdate()->first();

            if (!$locked || !in_array($locked->status, $fromStatuses, true) || $locked->isSettled()) {
                if ($locked) {
                    $this->setRawAttributes($locked->getAttributes(), true);
                    $this->syncOriginal();
                }

                return;
            }

            $locked->update(['status' => $status]);

            if ($collect && $locked->campaign_id) {
                $campaign = DonationCampaign::query()->whereKey($locked->campaign_id)->lockForUpdate()->first();
                $campaign?->increment('collected_amount', $locked->amount);
            }

            if ($beforeCommit) {
                $beforeCommit($locked);
            }

            $this->setRawAttributes($locked->fresh()->getAttributes(), true);
            $this->syncOriginal();
            $changed = true;
        });

        return $changed;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Dibayar',
            'failed' => 'Gagal',
            'cancelled' => 'Dibatalkan',
            'waiting_verification' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'paid', 'verified' => 'success',
            'pending', 'waiting_verification' => 'warning',
            'failed', 'cancelled', 'rejected' => 'danger',
            default => 'gray',
        };
    }
}
