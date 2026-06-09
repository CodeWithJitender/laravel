<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileStorageSetting extends Model
{
    protected $fillable = [
        'default_disk',
        's3_key',
        's3_secret',
        's3_region',
        's3_bucket',
    ];

    protected $casts = [
        's3_secret' => 'encrypted',
    ];
}
