<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Department;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('department.view');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('department.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('department.create');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('department.edit');
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->hasPermissionTo('department.delete');
    }
}
