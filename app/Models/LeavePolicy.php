<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeavePolicy extends Model
{
    protected $fillable = [
        'uuid',
        'leave_type_id',
        'annual_allocation',
        'monthly_accrual',
        'carry_forward_limit',
        'max_consecutive_days',
        'notice_period_days',
        'status',
    ];

    protected $casts = [
        'annual_allocation' => 'decimal:2',
        'carry_forward_limit' => 'decimal:2',
        'monthly_accrual' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(LeavePolicyRule::class, 'policy_id');
    }
}
