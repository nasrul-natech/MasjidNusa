<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'subject', 'body', 'placeholders', 'is_active'];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean',
    ];

    public function render(array $data = []): string
    {
        $brand = app(\App\Services\BrandingService::class);
        $data = array_merge([
            'app_name' => $brand->appName(),
            'mosque_name' => $brand->mosqueName(),
            'primary_color' => $brand->primaryColor(),
            'accent_color' => $brand->accentColor(),
        ], $data);

        $body = $this->body;
        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', (string) $value, $body);
        }

        return str_replace(
            ['#064e3b', '#c59d3f'],
            [$brand->primaryColor(), $brand->accentColor()],
            $body
        );
    }

    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->where('is_active', true)->first();
    }
}
