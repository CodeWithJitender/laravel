<?php

namespace App\Repositories\Eloquent;

use App\Models\Shift;
use App\Repositories\ShiftRepositoryInterface;

class EloquentShiftRepository extends BaseRepository implements ShiftRepositoryInterface
{
    public function __construct(Shift $model)
    {
        parent::__construct($model);
    }
}
