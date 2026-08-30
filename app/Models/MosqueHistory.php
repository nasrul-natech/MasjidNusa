<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MosqueHistory extends Model
{
    protected $fillable = [
        'mosque_profile_id', 'title', 'content', 'event_date', 'documentation', 'sort_order',
    ];

    protected $casts = [
        'event_date' => 'date',
        'documentation' => 'array',
    ];

    public function mosqueProfile()
    {
        return $this->belongsTo(MosqueProfile::class);
    }
}
