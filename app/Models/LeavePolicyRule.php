<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeavePolicyRule extends Model
{
    protected $fillable = ['policy_id', 'rule_type', 'rule_operator', 'rule_values'];

    protected $casts = [
        'rule_values' => 'array',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class, 'policy_id');
    }
}
