<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    protected $fillable = [
        'min_password_length',
        'password_expiry_days',
        'failed_login_attempts',
        'account_lock_minutes',
        'session_timeout_minutes',
    ];
}
