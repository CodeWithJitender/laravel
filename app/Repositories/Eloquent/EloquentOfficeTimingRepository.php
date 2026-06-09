<?php

namespace App\Repositories\Eloquent;

use App\Models\OfficeTiming;
use App\Repositories\OfficeTimingRepositoryInterface;

class EloquentOfficeTimingRepository extends BaseRepository implements OfficeTimingRepositoryInterface
{
    public function __construct(OfficeTiming $model)
    {
        parent::__construct($model);
    }
}
