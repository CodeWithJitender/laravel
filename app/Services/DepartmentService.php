<?php

namespace App\Services;

use App\Repositories\DepartmentRepositoryInterface;
use App\Models\DepartmentHead;
use Illuminate\Support\Facades\DB;
use Exception;

class DepartmentService extends BaseService
{
    protected $departmentRepo;

    public function __construct(DepartmentRepositoryInterface $departmentRepo)
    {
        $this->departmentRepo = $departmentRepo;
    }

    public function getPaginated(int $perPage = 15, ?string $search = '', ?string $status = '')
    {
        $search = $search ?? '';
        $status = $status ?? '';

        $query = \App\Models\Department::with('head.user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('department_name', 'like', "%{$search}%")
                  ->orWhere('department_code', 'like', "%{$search}%");
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    public function getAllActive()
    {
        return $this->departmentRepo->all(['*'], ['head.user'])->where('status', 'active');
    }

    public function findById(int $id)
    {
        return $this->departmentRepo->findOrFail($id, ['*'], ['head.user']);
    }

    public function createDepartment(array $data)
    {
        return $this->transaction(function () use ($data) {
            $headId = $data['head_employee_id'] ?? null;
            unset($data['head_employee_id']);

            $department = $this->departmentRepo->create($data);

            if ($headId) {
                DepartmentHead::create([
                    'department_id' => $department->id,
                    'user_id' => $headId
                ]);
            }

            return $department;
        });
    }

    public function updateDepartment(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {
            $department = $this->departmentRepo->findOrFail($id);
            
            $headId = $data['head_employee_id'] ?? null;
            unset($data['head_employee_id']);

            $this->departmentRepo->updateModel($department, $data);

            if ($headId) {
                DepartmentHead::updateOrCreate(
                    ['department_id' => $department->id],
                    ['user_id' => $headId]
                );
            } else {
                DepartmentHead::where('department_id', $department->id)->delete();
            }

            return $department;
        });
    }

    public function deleteDepartment(int $id): bool
    {
        return $this->transaction(function () use ($id) {
            $hasEmployees = DB::table('employee_details')
                ->join('users', 'employee_details.user_id', '=', 'users.id')
                ->where('employee_details.department_id', $id)
                ->whereNull('users.deleted_at')
                ->whereNull('employee_details.deleted_at')
                ->exists();

            if ($hasEmployees) {
                throw new Exception("Cannot delete department because employees are currently assigned to it.");
            }

            // Remove department head mapping first
            DepartmentHead::where('department_id', $id)->delete();

            return $this->departmentRepo->delete($id);
        });
    }
}
