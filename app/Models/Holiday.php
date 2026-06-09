<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Str;

class Holiday extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'uuid',
        'holiday_name',
        'holiday_code',
        'description',
        'holiday_date',
        'holiday_type_id',
        'is_paid',
        'status',
        'publish_at',
        'created_by',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_paid' => 'boolean',
        'publish_at' => 'datetime',
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

    public function holidayType(): BelongsTo
    {
        return $this->belongsTo(HolidayType::class, 'holiday_type_id');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'holiday_locations');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }



    public function reminders(): HasMany
    {
        return $this->hasMany(HolidayReminder::class, 'holiday_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(HolidayNotification::class, 'holiday_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
