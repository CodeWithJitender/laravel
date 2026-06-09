<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('report.view') || $user->hasRole('Admin');
    }

    public function view(User $user, $model = null): bool
    {
        return $user->hasPermissionTo('report.view') || $user->hasRole('Admin');
    }

    public function generate(User $user): bool
    {
        return $user->hasPermissionTo('report.generate') || $user->hasRole('Admin');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('report.export') || $user->hasRole('Admin');
    }

    public function schedule(User $user): bool
    {
        return $user->hasPermissionTo('report.schedule') || $user->hasRole('Admin');
    }

    public function manageTemplates(User $user): bool
    {
        return $user->hasPermissionTo('report.template.manage') || $user->hasRole('Admin');
    }

    public function viewAnalytics(User $user): bool
    {
        return $user->hasPermissionTo('analytics.view') || $user->hasRole('Admin');
    }

    public function viewExecutiveReports(User $user): bool
    {
        return $user->hasPermissionTo('executive_report.view') || $user->hasRole('Admin');
    }
}
