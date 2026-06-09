<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReportTemplate extends Model
{
    protected $fillable = [
        'uuid',
        'report_definition_id',
        'template_name',
        'is_custom',
        'custom_columns',
        'custom_filters',
        'status',
        'created_by',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
        'custom_columns' => 'array',
        'custom_filters' => 'array',
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

    public function reportDefinition(): BelongsTo
    {
        return $this->belongsTo(ReportDefinition::class, 'report_definition_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
