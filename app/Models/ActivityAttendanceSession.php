<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityAttendanceSession extends Model
{
    protected $fillable = ['activity_id', 'completed_at', 'opened_at', 'closes_at', 'duration_seconds', 'opened_by'];

    protected $casts = ['completed_at' => 'datetime', 'opened_at' => 'datetime', 'closes_at' => 'datetime', 'duration_seconds' => 'integer'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function attendances()
    {
        return $this->hasMany(ActivityAttendance::class, 'session_id');
    }
}
