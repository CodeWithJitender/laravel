<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReportCategory extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'code',
        'description',
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

    public function definitions(): HasMany
    {
        return $this->hasMany(ReportDefinition::class, 'category_id');
    }
}
