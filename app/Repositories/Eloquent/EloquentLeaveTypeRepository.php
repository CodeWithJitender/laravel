<?php

namespace App\Repositories\Eloquent;

use App\Models\LeaveType;
use App\Repositories\LeaveTypeRepositoryInterface;

class EloquentLeaveTypeRepository extends BaseRepository implements LeaveTypeRepositoryInterface
{
    public function __construct(LeaveType $model)
    {
        parent::__construct($model);
    }
}
