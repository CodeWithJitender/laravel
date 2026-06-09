<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Shift;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('shift.view');
    }

    public function view(User $user, Shift $shift): bool
    {
        return $user->hasPermissionTo('shift.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('shift.create');
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->hasPermissionTo('shift.edit');
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $user->hasPermissionTo('shift.delete');
    }
}
