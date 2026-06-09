<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PayrollRun extends Model
{
    protected $fillable = [
        'uuid',
        'run_month',
        'run_year',
        'run_type',
        'status',
        'processed_by',
        'processed_at',
        'approved_by',
        'approved_at',
        'total_employees',
        'total_gross',
        'total_earnings',
        'total_deductions',
        'total_net',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',
        'total_gross' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(PayrollRunEmployee::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PayrollApproval::class);
    }
}
