<?php

namespace App\Repositories\Eloquent;

use App\Models\AttendanceMonthlySummary;
use App\Repositories\AttendanceMonthlySummaryRepositoryInterface;

class EloquentAttendanceMonthlySummaryRepository extends BaseRepository implements AttendanceMonthlySummaryRepositoryInterface
{
    public function __construct(AttendanceMonthlySummary $model)
    {
        parent::__construct($model);
    }
}
