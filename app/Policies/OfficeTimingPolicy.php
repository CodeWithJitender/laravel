<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OfficeTiming;

class OfficeTimingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('office_timing.manage') || $user->hasRole('Admin');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermissionTo('office_timing.manage') || $user->hasRole('Admin');
    }
}
