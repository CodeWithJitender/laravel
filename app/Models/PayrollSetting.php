<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    protected $fillable = [
        'payroll_cycle',
        'processing_day',
        'pf_percentage',
        'professional_tax_threshold',
    ];

    protected $casts = [
        'pf_percentage' => 'decimal:2',
        'professional_tax_threshold' => 'decimal:2',
    ];
}
