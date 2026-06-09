<?php

namespace App\Services;

use App\Repositories\DesignationRepositoryInterface;
use App\Models\OrganizationalHierarchy;
use Illuminate\Support\Facades\DB;
use Exception;

class DesignationService extends BaseService
{
    protected $designationRepo;

    public function __construct(DesignationRepositoryInterface $designationRepo)
    {
        $this->designationRepo = $designationRepo;
    }

    public function getPaginated(int $perPage = 15, string $search = '', string $status = '')
    {
        $query = \App\Models\Designation::with('hierarchy.parentDesignation');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('designation_name', 'like', "%{$search}%")
                  ->orWhere('designation_code', 'like', "%{$search}%");
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    public function getAllActive()
    {
        return $this->designationRepo->all(['*'], ['hierarchy.parentDesignation'])->where('status', 'active');
    }

    public function findById(int $id)
    {
        return $this->designationRepo->findOrFail($id, ['*'], ['hierarchy.parentDesignation']);
    }

    public function createDesignation(array $data)
    {
        return $this->transaction(function () use ($data) {
            $parentDesignationId = $data['parent_designation_id'] ?? null;
            unset($data['parent_designation_id']);

            $designation = $this->designationRepo->create($data);

            if ($parentDesignationId) {
                OrganizationalHierarchy::create([
                    'designation_id' => $designation->id,
                    'parent_designation_id' => $parentDesignationId
                ]);
            }

            return $designation;
        });
    }

    public function updateDesignation(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {
            $designation = $this->designationRepo->findOrFail($id);
            
            $parentDesignationId = $data['parent_designation_id'] ?? null;
            unset($data['parent_designation_id']);

            $this->designationRepo->updateModel($designation, $data);

            if ($parentDesignationId) {
                OrganizationalHierarchy::updateOrCreate(
                    ['designation_id' => $designation->id],
                    ['parent_designation_id' => $parentDesignationId]
                );
            } else {
                OrganizationalHierarchy::where('designation_id', $designation->id)->delete();
            }

            return $designation;
        });
    }

    public function deleteDesignation(int $id): bool
    {
        return $this->transaction(function () use ($id) {
            $assignedEmployees = DB::table('employee_details')
                ->join('users', 'employee_details.user_id', '=', 'users.id')
                ->where('employee_details.designation_id', $id)
                ->whereNull('employee_details.deleted_at')
                ->pluck('users.name')
                ->toArray();

            if (!empty($assignedEmployees)) {
                $names = implode(', ', $assignedEmployees);
                throw new Exception("Cannot delete designation because employees are currently assigned to it: {$names}");
            }

            // Remove hierarchy link
            OrganizationalHierarchy::where('designation_id', $id)->delete();
            // Remove as parent in hierarchy
            OrganizationalHierarchy::where('parent_designation_id', $id)->update(['parent_designation_id' => null]);

            return $this->designationRepo->delete($id);
        });
    }
}
