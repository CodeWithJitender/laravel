<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    protected $fillable = [
        'backup_frequency',
        'backup_time',
        'include_files',
        'retention_days',
    ];

    protected $casts = [
        'include_files' => 'boolean',
    ];
}
