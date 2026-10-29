<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    protected $fillable = [
        'transaction_id', 'image', 'notes', 'uploaded_at', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? route('donations.proof', $this) : null;
    }
}
