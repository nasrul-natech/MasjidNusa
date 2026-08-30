<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementHistory extends Model
{
    protected $fillable = [
        'title', 'description', 'start_year', 'end_year', 'is_current', 'photo',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function organizationStructures()
    {
        return $this->hasMany(OrganizationStructure::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function allStructures()
    {
        return $this->hasMany(OrganizationStructure::class)->orderBy('sort_order');
    }

    public function getPeriodAttribute(): string
    {
        return $this->start_year . ($this->end_year ? ' - ' . $this->end_year : ' - Sekarang');
    }
}
