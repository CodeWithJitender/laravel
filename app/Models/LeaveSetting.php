<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveSetting extends Model
{
    protected $fillable = [
        'accrual_cycle',
        'carry_forward_enabled',
        'max_accumulated_days',
    ];

    protected $casts = [
        'carry_forward_enabled' => 'boolean',
    ];
}
