<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Repositories\AttendanceRepositoryInterface;

class EloquentAttendanceRepository extends BaseRepository implements AttendanceRepositoryInterface
{
    public function __construct(Attendance $model)
    {
        parent::__construct($model);
    }
}
