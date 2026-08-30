<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'donation_id', 'payment_method_id', 'amount', 'reference_number',
        'status', 'gateway_response', 'payment_url', 'paid_at', 'expired_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function proof()
    {
        return $this->hasOne(PaymentProof::class, 'transaction_id');
    }
}
