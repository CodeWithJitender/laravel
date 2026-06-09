<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'opening_balance',
        'allocated_balance',
        'accrued_balance',
        'used_balance',
        'pending_balance',
        'carry_forward_balance',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'allocated_balance' => 'decimal:2',
        'accrued_balance' => 'decimal:2',
        'used_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'carry_forward_balance' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getRemainingBalanceAttribute(): float
    {
        return (float) ($this->allocated_balance + $this->accrued_balance + $this->carry_forward_balance - $this->used_balance);
    }
}
