<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    protected $fillable = ['uuid', 'key', 'name', 'subject', 'content', 'channels', 'status'];

    protected $casts = [
        'channels' => 'array',
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

    public function events(): HasMany
    {
        return $this->hasMany(NotificationEvent::class, 'template_id');
    }
}
