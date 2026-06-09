<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Holiday;

class HolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('holiday.view');
    }

    public function view(User $user, Holiday $holiday): bool
    {
        return $user->hasPermissionTo('holiday.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('holiday.create');
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return $user->hasPermissionTo('holiday.edit');
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return $user->hasPermissionTo('holiday.delete');
    }

    public function publish(User $user, Holiday $holiday): bool
    {
        return $user->hasPermissionTo('holiday.publish');
    }
}
