<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $fillable = [
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'encryption',
        'sender_name',
        'sender_email',
    ];

    protected $casts = [
        'smtp_password' => 'encrypted',
    ];
}
