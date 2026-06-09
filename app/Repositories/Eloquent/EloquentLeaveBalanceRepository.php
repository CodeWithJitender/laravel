<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveBalance;
use App\Repositories\LeaveBalanceRepositoryInterface;

class EloquentLeaveBalanceRepository extends BaseRepository implements LeaveBalanceRepositoryInterface
{
    public function __construct(LeaveBalance $model)
    {
        parent::__construct($model);
    }
}
