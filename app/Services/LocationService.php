<?php

namespace App\Services;

use App\Repositories\LocationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class LocationService extends BaseService
{
    protected $locationRepo;

    public function __construct(LocationRepositoryInterface $locationRepo)
    {
        $this->locationRepo = $locationRepo;
    }

    public function getPaginated(int $perPage = 15, ?string $search = '', ?string $status = '')
    {
        $search = $search ?? '';
        $status = $status ?? '';

        $query = \App\Models\Location::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('location_name', 'like', "%{$search}%")
                  ->orWhere('location_code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    public function getAllActive()
    {
        return $this->locationRepo->all()->where('status', 'active');
    }

    public function findById(int $id)
    {
        return $this->locationRepo->findOrFail($id);
    }

    public function createLocation(array $data)
    {
        return $this->locationRepo->create($data);
    }

    public function updateLocation(int $id, array $data)
    {
        return $this->locationRepo->update($id, $data);
    }

    public function deleteLocation(int $id): bool
    {
        return $this->transaction(function () use ($id) {
            $hasEmployees = DB::table('employee_details')
                ->join('users', 'employee_details.user_id', '=', 'users.id')
                ->where('employee_details.location_id', $id)
                ->whereNull('users.deleted_at')
                ->whereNull('employee_details.deleted_at')
                ->exists();

            if ($hasEmployees) {
                throw new Exception("Cannot delete location because employees are currently assigned to it.");
            }

            return $this->locationRepo->delete($id);
        });
    }
}
