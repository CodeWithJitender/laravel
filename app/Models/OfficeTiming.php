<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class OfficeTiming extends Model
{
    use LogsActivity;

    protected $table = 'office_timings';

    protected $fillable = [
        'uuid',
        'name',
        'working_days',
        'start_time',
        'end_time',
        'minimum_hours',
        'half_day_hours',
        'weekly_off',
        'status',
    ];

    protected $casts = [
        'working_days' => 'array',
        'weekly_off' => 'array',
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
}
