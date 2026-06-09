<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SalaryRevision extends Model
{
    protected $fillable = [
        'uuid',
        'employee_id',
        'old_gross_salary',
        'new_gross_salary',
        'old_annual_ctc',
        'new_annual_ctc',
        'revision_date',
        'effective_date',
        'reason',
        'approved_by',
    ];

    protected $casts = [
        'revision_date' => 'date',
        'effective_date' => 'date',
        'old_gross_salary' => 'decimal:2',
        'new_gross_salary' => 'decimal:2',
        'old_annual_ctc' => 'decimal:2',
        'new_annual_ctc' => 'decimal:2',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
