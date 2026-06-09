<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class SalaryComponent extends Model
{
    protected $fillable = [
        'uuid',
        'component_name',
        'component_code',
        'component_type',
        'calculation_type',
        'default_value',
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

    public function salaryStructures(): BelongsToMany
    {
        return $this->belongsToMany(SalaryStructure::class, 'salary_structure_components')
            ->withPivot(['calculation_value', 'calculation_formula', 'sort_order']);
    }
}
