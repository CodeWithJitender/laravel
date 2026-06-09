<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationalHierarchy extends Model
{
    protected $table = 'organizational_hierarchy';

    public $timestamps = false;

    protected $fillable = [
        'designation_id',
        'parent_designation_id',
    ];

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function parentDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'parent_designation_id');
    }
}
