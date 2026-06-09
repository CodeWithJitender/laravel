<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SalaryStructure extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'status',
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

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(SalaryComponent::class, 'salary_structure_components')
            ->withPivot(['calculation_value', 'calculation_formula', 'sort_order'])
            ->orderByPivot('sort_order', 'asc');
    }

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(EmployeeSalaryStructure::class);
    }
}
