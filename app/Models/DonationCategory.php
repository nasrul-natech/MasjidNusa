<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function campaigns()
    {
        return $this->hasMany(DonationCampaign::class, 'category_id');
    }
}
