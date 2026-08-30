<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DuitkuConfig extends Model
{
    protected $fillable = [
        'merchant_code', 'api_key', 'callback_url', 'return_url',
        'expiry_period', 'environment', 'is_active',
    ];

    protected $hidden = ['api_key'];

    protected $casts = ['is_active' => 'boolean'];

    public static function active(): ?self
    {
        return self::where('is_active', true)->first();
    }

    public function getBaseUrlAttribute(): string
    {
        return $this->environment === 'production'
            ? 'https://passport.duitku.com/webapi'
            : 'https://sandbox.duitku.com/webapi';
    }
}
