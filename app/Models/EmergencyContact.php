<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyContact extends Model
{
    protected $fillable = [
        'employee_detail_id',
        'name',
        'relationship',
        'phone',
        'email',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function employeeDetail(): BelongsTo
    {
        return $this->belongsTo(EmployeeDetail::class);
    }
}
