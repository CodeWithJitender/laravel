<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReportDefinition extends Model
{
    protected $fillable = [
        'uuid',
        'report_name',
        'report_code',
        'category_id',
        'description',
        'query_builder_config',
        'default_columns',
        'status',
        'created_by',
    ];

    protected $casts = [
        'query_builder_config' => 'array',
        'default_columns' => 'array',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ReportCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function filters(): HasMany
    {
        return $this->hasMany(ReportFilter::class, 'report_definition_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(ReportTemplate::class, 'report_definition_id');
    }

    public function scheduledReports(): HasMany
    {
        return $this->hasMany(ScheduledReport::class, 'report_definition_id');
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ReportExport::class, 'report_definition_id');
    }

    public function executionLogs(): HasMany
    {
        return $this->hasMany(ReportExecutionLog::class, 'report_definition_id');
    }

    public function savedReports(): HasMany
    {
        return $this->hasMany(SavedReport::class, 'report_definition_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(FavoriteReport::class, 'report_definition_id');
    }
}
