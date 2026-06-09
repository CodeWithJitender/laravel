<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LeaveRequest;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('leave.view') || $user->hasPermissionTo('leave.view_self');
    }

    public function view(User $user, LeaveRequest $request): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->id === $request->employee_id) {
            return $user->hasPermissionTo('leave.view_self');
        }

        // Manager check
        $detail = $request->employee->employeeDetail;
        if ($detail && $detail->manager_id === $user->id) {
            return $user->hasPermissionTo('leave.view');
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('leave.create');
    }

    public function cancel(User $user, LeaveRequest $request): bool
    {
        return $user->id === $request->employee_id && $request->status === 'pending';
    }

    public function approve(User $user, LeaveRequest $request): bool
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if ($user->hasPermissionTo('leave.approve')) {
            $detail = $request->employee->employeeDetail;
            return $detail && $detail->manager_id === $user->id;
        }

        return false;
    }

    public function reject(User $user, LeaveRequest $request): bool
    {
        return $this->approve($user, $request);
    }
}
