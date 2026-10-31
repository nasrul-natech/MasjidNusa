<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MosqueDisplay extends Model
{
    public $incrementing = false;

    protected $fillable = ['id', 'draft', 'published', 'version', 'published_at'];

    protected $casts = ['draft' => 'array', 'published' => 'array', 'published_at' => 'datetime'];

    public function items()
    {
        return $this->hasMany(MosqueDisplayItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
