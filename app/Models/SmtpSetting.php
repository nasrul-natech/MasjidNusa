<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    protected $fillable = [
        'host', 'port', 'username', 'password', 'encryption',
        'sender_name', 'sender_email', 'is_active',
    ];

    protected $hidden = ['password'];

    protected $casts = ['is_active' => 'boolean'];

    public static function active(): ?self
    {
        return self::where('is_active', true)->first();
    }
}
