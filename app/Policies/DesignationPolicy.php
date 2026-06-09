<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Designation;

class DesignationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('designation.view');
    }

    public function view(User $user, Designation $designation): bool
    {
        return $user->hasPermissionTo('designation.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('designation.create');
    }

    public function update(User $user, Designation $designation): bool
    {
        return $user->hasPermissionTo('designation.edit');
    }

    public function delete(User $user, Designation $designation): bool
    {
        return $user->hasPermissionTo('designation.delete');
    }
}
