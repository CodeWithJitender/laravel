<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Shift extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'uuid',
        'shift_code',
        'shift_name',
        'description',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'break_minutes',
        'status',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function employeeDetails(): HasMany
    {
        return $this->hasMany(EmployeeDetail::class, 'shift_id');
    }
}
