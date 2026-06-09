<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Location;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('location.view');
    }

    public function view(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('location.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('location.create');
    }

    public function update(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('location.edit');
    }

    public function delete(User $user, Location $location): bool
    {
        return $user->hasPermissionTo('location.delete');
    }
}
