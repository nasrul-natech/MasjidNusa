<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityAttendanceSetting extends Model
{
    protected $fillable = ['activity_id', 'enabled', 'duration_seconds'];

    protected $casts = ['enabled' => 'boolean', 'duration_seconds' => 'integer'];
}
