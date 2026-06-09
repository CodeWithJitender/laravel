<?php

namespace App\Repositories\Eloquent;

use App\Models\Holiday;
use App\Models\User;
use App\Repositories\HolidayRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentHolidayRepository extends BaseRepository implements HolidayRepositoryInterface
{
    public function __construct(Holiday $model)
    {
        parent::__construct($model);
    }

    public function getActiveHolidaysForLocation(int $locationId): Collection
    {
        return $this->model->newQuery()
            ->where('status', 'published')
            ->where(function ($query) use ($locationId) {
                // If it is mapped to locations, it must be mapped to this location ID
                $query->whereHas('locations', function ($q) use ($locationId) {
                    $q->where('locations.id', $locationId);
                })
                // OR it has no locations mapped (meaning it is national/company-wide and applies to all locations)
                ->orWhereDoesntHave('locations');
            })
            ->orderBy('holiday_date', 'asc')
            ->get();
    }

    public function getActiveHolidaysForUser(User $user): Collection
    {
        $locationId = $user->employeeDetail ? $user->employeeDetail->location_id : null;
        if (!$locationId) {
            // Fallback: only national/company-wide holidays
            return $this->model->newQuery()
                ->where('status', 'published')
                ->whereDoesntHave('locations')
                ->orderBy('holiday_date', 'asc')
                ->get();
        }

        return $this->getActiveHolidaysForLocation($locationId);
    }

    public function getUpcomingHolidaysForUser(User $user, int $limit = 5): Collection
    {
        $locationId = $user->employeeDetail ? $user->employeeDetail->location_id : null;
        
        $query = $this->model->newQuery()
            ->where('status', 'published')
            ->where('holiday_date', '>=', now()->toDateString());

        if ($locationId) {
            $query->where(function ($q) use ($locationId) {
                $q->whereHas('locations', function ($l) use ($locationId) {
                    $l->where('locations.id', $locationId);
                })->orWhereDoesntHave('locations');
            });
        } else {
            $query->whereDoesntHave('locations');
        }

        return $query->orderBy('holiday_date', 'asc')
            ->limit($limit)
            ->get();
    }

    public function paginateHolidays(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['holidayType', 'locations']);

        if (!empty($filters['year'])) {
            $query->whereYear('holiday_date', $filters['year']);
        }

        if (!empty($filters['location_id'])) {
            $locId = $filters['location_id'];
            $query->whereHas('locations', function ($q) use ($locId) {
                $q->where('locations.id', $locId);
            });
        }

        if (!empty($filters['holiday_type_id'])) {
            $query->where('holiday_type_id', $filters['holiday_type_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', '!=', 'archived');
        }

        return $query->orderBy('holiday_date', 'asc')->paginate($perPage);
    }
}
