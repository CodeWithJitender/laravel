<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveCarryForward extends Model
{
    protected $table = 'leave_carry_forwards';

    public $timestamps = false;

    protected $fillable = ['employee_id', 'leave_type_id', 'amount_carried', 'amount_expired', 'run_year'];

    protected $casts = [
        'amount_carried' => 'decimal:2',
        'amount_expired' => 'decimal:2',
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
