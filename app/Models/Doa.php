<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Doa extends Model
{
    protected $table = 'duas';

    protected $fillable = [
        'doa_category_id', 'title', 'slug', 'arabic', 'latin',
        'translation', 'content_html', 'content_text', 'source',
        'status', 'sort_order',
    ];

    public function category()
    {
        return $this->belongsTo(DoaCategory::class, 'doa_category_id');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'user_doa_favorites', 'doa_id', 'user_id')
            ->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'publish');
    }
}
