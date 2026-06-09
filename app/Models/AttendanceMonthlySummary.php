<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceMonthlySummary extends Model
{
    protected $table = 'attendance_monthly_summaries';

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'present_days',
        'absent_days',
        'late_days',
        'leave_days',
        'holiday_days',
        'wfh_days',
        'missed_punch_days',
        'total_working_hours',
        'total_overtime_hours',
    ];

    protected $casts = [
        'total_working_hours' => 'decimal:2',
        'total_overtime_hours' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
