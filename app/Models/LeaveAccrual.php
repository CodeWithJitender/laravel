<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAccrual extends Model
{
    public $timestamps = false;

    protected $fillable = ['employee_id', 'leave_type_id', 'accrued_amount', 'run_date'];

    protected $casts = [
        'run_date' => 'date',
        'accrued_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
