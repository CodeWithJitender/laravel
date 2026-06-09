<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestDay extends Model
{
    public $timestamps = false;

    protected $fillable = ['leave_request_id', 'leave_date', 'day_weight', 'session'];

    protected $casts = [
        'leave_date' => 'date',
        'day_weight' => 'decimal:1',
    ];

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
