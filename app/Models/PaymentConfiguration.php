<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentConfiguration extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    public static function getValue(string $key, $default = null)
    {
        $config = self::where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    public static function setValue(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function isManualEnabled(): bool
    {
        $method = self::getValue('active_payment_method', 'both');
        return in_array($method, ['manual', 'both']);
    }

    public static function isDuitkuEnabled(): bool
    {
        $method = self::getValue('active_payment_method', 'both');
        return in_array($method, ['duitku', 'both']);
    }
}
