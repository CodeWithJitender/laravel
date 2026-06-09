<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveStatusHistory extends Model
{
    protected $table = 'leave_status_history';

    public $timestamps = false;

    protected $fillable = ['leave_request_id', 'user_id', 'status', 'remarks'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
