<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HolidayType extends Model
{
    protected $table = 'holiday_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class, 'holiday_type_id');
    }
}
