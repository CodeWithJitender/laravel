<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'app_name',
        'app_version',
        'default_timezone',
        'default_currency',
        'date_format',
        'time_format',
        'language',
        'system_status',
    ];
}
