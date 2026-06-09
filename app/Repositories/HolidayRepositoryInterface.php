<?php

namespace App\Repositories;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface HolidayRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveHolidaysForLocation(int $locationId): Collection;
    
    public function getActiveHolidaysForUser(User $user): Collection;

    public function getUpcomingHolidaysForUser(User $user, int $limit = 5): Collection;

    public function paginateHolidays(array $filters, int $perPage = 15): LengthAwarePaginator;
}
