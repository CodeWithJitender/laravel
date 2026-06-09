<?php

namespace App\Repositories\Eloquent;

use App\Models\Designation;
use App\Repositories\DesignationRepositoryInterface;

class EloquentDesignationRepository extends BaseRepository implements DesignationRepositoryInterface
{
    public function __construct(Designation $model)
    {
        parent::__construct($model);
    }
}
