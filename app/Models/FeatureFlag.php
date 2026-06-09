<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = [
        'flag_key',
        'flag_value',
        'description',
    ];

    protected $casts = [
        'flag_value' => 'boolean',
    ];
}
