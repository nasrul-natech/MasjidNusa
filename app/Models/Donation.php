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
     * Pindah ke status lunas sekali saja, lalu naikkan saldo kampanye.
     * Pemanggilan ulang pada donasi yang sudah lunas tidak mengubah saldo.
     */
    public function settle(string $status, ?callable $beforeCommit = null): bool
    {
        $changed = false;

        DB::transaction(function () use ($status, $beforeCommit, &$changed) {
            $locked = static::query()->whereKey($this->getKey())->lockForUpdate()->first();

            if (!$locked || $locked->isSettled()) {
                if ($locked) {
                    $this->setRawAttributes($locked->getAttributes(), true);
                    $this->syncOriginal();
                }

                return;
            }

            $locked->update(['status' => $status]);

            if ($locked->campaign_id) {
                DonationCampaign::query()
                    ->whereKey($locked->campaign_id)
                    ->increment('collected_amount', $locked->amount);
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

    /**
     * Tutup donasi yang belum lunas. Status final tidak ditimpa dan saldo tidak dikurangi.
     */
    public function closeIfOpen(string $status, ?callable $beforeCommit = null): bool
    {
        $changed = false;

        DB::transaction(function () use ($status, $beforeCommit, &$changed) {
            $locked = static::query()->whereKey($this->getKey())->lockForUpdate()->first();

            if (!$locked || $locked->isSettled()) {
                if ($locked) {
                    $this->setRawAttributes($locked->getAttributes(), true);
                    $this->syncOriginal();
                }

                return;
            }

            $locked->update(['status' => $status]);

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
