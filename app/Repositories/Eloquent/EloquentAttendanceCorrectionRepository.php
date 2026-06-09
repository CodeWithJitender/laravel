<?php

namespace App\Repositories\Eloquent;

use App\Models\AttendanceCorrection;
use App\Repositories\AttendanceCorrectionRepositoryInterface;

class EloquentAttendanceCorrectionRepository extends BaseRepository implements AttendanceCorrectionRepositoryInterface
{
    public function __construct(AttendanceCorrection $model)
    {
        parent::__construct($model);
    }
}
