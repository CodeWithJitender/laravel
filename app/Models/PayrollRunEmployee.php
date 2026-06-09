<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayrollRunEmployee extends Model
{
    protected $table = 'payroll_run_employees';

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'salary_structure_id',
        'monthly_gross_salary',
        'total_working_days',
        'paid_days',
        'lop_days',
        'gross_salary_earned',
        'total_earnings',
        'total_deductions',
        'net_salary',
        'status',
    ];

    protected $casts = [
        'monthly_gross_salary' => 'decimal:2',
        'paid_days' => 'decimal:2',
        'lop_days' => 'decimal:2',
        'gross_salary_earned' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    public function payslip(): HasOne
    {
        return $this->hasOne(Payslip::class);
    }
}
