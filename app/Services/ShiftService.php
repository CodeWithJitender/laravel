<?php

namespace App\Services;

use App\Repositories\ShiftRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class ShiftService extends BaseService
{
    protected $shiftRepo;

    public function __construct(ShiftRepositoryInterface $shiftRepo)
    {
        $this->shiftRepo = $shiftRepo;
    }

    public function getPaginated(int $perPage = 15, ?string $search = '', ?string $status = '')
    {
        $search = $search ?? '';
        $status = $status ?? '';

        $query = \App\Models\Shift::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('shift_name', 'like', "%{$search}%")
                  ->orWhere('shift_code', 'like', "%{$search}%");
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    public function getAllActive()
    {
        return $this->shiftRepo->all()->where('status', 'active');
    }

    public function findById(int $id)
    {
        return $this->shiftRepo->findOrFail($id);
    }

    public function createShift(array $data)
    {
        return $this->shiftRepo->create($data);
    }

    public function updateShift(int $id, array $data)
    {
        return $this->shiftRepo->update($id, $data);
    }

    public function deleteShift(int $id): bool
    {
        return $this->transaction(function () use ($id) {
            $hasEmployees = DB::table('employee_details')
                ->join('users', 'employee_details.user_id', '=', 'users.id')
                ->where('employee_details.shift_id', $id)
                ->whereNull('users.deleted_at')
                ->whereNull('employee_details.deleted_at')
                ->exists();

            if ($hasEmployees) {
                throw new Exception("Cannot delete shift because employees are currently assigned to it.");
            }

            return $this->shiftRepo->delete($id);
        });
    }
}
