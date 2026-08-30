<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoaCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order'];

    public function duas()
    {
        return $this->hasMany(Doa::class, 'doa_category_id');
    }

    public function publishedDuas()
    {
        return $this->duas()->where('status', 'publish');
    }
}
