<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AttendanceCorrection;

class CorrectionPolicy
{
    public function request(User $user): bool
    {
        return $user->hasPermissionTo('attendance.correction.request');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('attendance.view') || $user->hasPermissionTo('attendance.correction.approve');
    }

    public function approve(User $user, AttendanceCorrection $correction): bool
    {
        // Managers can approve if they have permission and are the employee's manager (or if they are Admin)
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->hasPermissionTo('attendance.correction.approve')) {
            $employeeDetail = $correction->user->employeeDetail;
            return $employeeDetail && $employeeDetail->manager_id === $user->id;
        }

        return false;
    }
}
