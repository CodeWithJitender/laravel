<?php

namespace App\Repositories\Eloquent;

use App\Models\LeavePolicy;
use App\Repositories\LeavePolicyRepositoryInterface;

class EloquentLeavePolicyRepository extends BaseRepository implements LeavePolicyRepositoryInterface
{
    public function __construct(LeavePolicy $model)
    {
        parent::__construct($model);
    }
}
