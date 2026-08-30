<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemModule extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active', 'is_core'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_core' => 'boolean',
    ];

    public static function isActive(string $slug): bool
    {
        return Cache::remember("module_{$slug}", 3600, function () use ($slug) {
            $module = self::where('slug', $slug)->first();
            return $module ? $module->is_active : true;
        });
    }
}
