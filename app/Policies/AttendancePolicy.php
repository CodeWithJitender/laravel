<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('attendance.view');
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->hasRole('Manager')) {
            return $user->hasPermissionTo('attendance.view') &&
                   $attendance->user->employeeDetail?->manager_id === $user->id;
        }

        return $user->id === $attendance->user_id && $user->hasPermissionTo('attendance.view_self');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('attendance.create');
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $user->hasPermissionTo('attendance.edit');
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->hasPermissionTo('attendance.delete');
    }
}
