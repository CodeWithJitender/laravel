<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'uuid',
        'employee_id',
        'principal_amount',
        'remaining_principal',
        'monthly_emi',
        'interest_rate',
        'disbursal_date',
        'status',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'remaining_principal' => 'decimal:2',
        'monthly_emi' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'disbursal_date' => 'date',
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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }
}
