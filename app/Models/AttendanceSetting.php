<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    protected $fillable = [
        'default_shift_id',
        'grace_period_minutes',
        'minimum_working_hours',
        'half_day_working_hours',
        'overtime_multiplier',
    ];

    protected $casts = [
        'minimum_working_hours' => 'decimal:2',
        'half_day_working_hours' => 'decimal:2',
        'overtime_multiplier' => 'decimal:2',
    ];
}
