<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Employees
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',

            // Attendance
            'attendance.view',
            'attendance.create',
            'attendance.edit',
            'attendance.delete',
            'attendance.manage',
            'attendance.view_self',
            'attendance.correct',
            'attendance.correction.request',
            'attendance.correction.approve',
            'attendance.report.view',

            // Leaves
            'leave.view',
            'leave.view_self',
            'leave.create',
            'leave.cancel',
            'leave.approve',
            'leave.reject',
            'leave.policy.manage',
            'leave.type.manage',
            'leave.report.view',

            // Holidays
            'holiday.view',
            'holiday.create',
            'holiday.edit',
            'holiday.delete',
            'holiday.publish',
            'holiday.report.view',
            'holiday.reminder.manage',


            // Announcements
            'announcements.manage',
            'announcements.view',
            'announcement.view',
            'announcement.create',
            'announcement.edit',
            'announcement.publish',
            'announcement.delete',

            // Notifications
            'notification.view',
            'notification.manage',
            'notification.template.manage',

            // Payroll
            'payroll.view',
            'payroll.process',
            'payroll.approve',
            'payroll.publish',
            'payroll.structure.manage',
            'payroll.revision.manage',
            'payroll.payslip.view_self',

            // Reports
            'reports.view',
            'reports.view_team',

            // System access
            'roles.manage',
            'settings.manage',

            // Organization Setup
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',

            'designation.view',
            'designation.create',
            'designation.edit',
            'designation.delete',

            'location.view',
            'location.create',
            'location.edit',
            'location.delete',

            'shift.view',
            'shift.create',
            'shift.edit',
            'shift.delete',

            'office_timing.manage',
            'org_structure.view',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign permissions
        $adminRole = Role::findOrCreate('Admin');
        $adminRole->update(['description' => 'Full system administrative access']);
        $adminRole->givePermissionTo(Permission::all());

        $managerRole = Role::findOrCreate('Manager');
        $managerRole->update(['description' => 'Controls team direct reports, approvals, and metrics']);
        $managerRole->givePermissionTo([
            'employees.view',
            'attendance.view',
            'attendance.create',
            'attendance.edit',
            'attendance.delete',
            'attendance.manage',
            'attendance.view_self',
            'attendance.correct',
            'attendance.correction.request',
            'attendance.correction.approve',
            'attendance.report.view',
            'leave.view',
            'leave.view_self',
            'leave.create',
            'leave.cancel',
            'leave.approve',
            'leave.reject',
            'leave.report.view',
            'holiday.view',
            'holiday.report.view',

            'announcements.view',
            'announcement.view',
            'announcement.create',
            'announcement.edit',
            'announcement.publish',
            'announcement.delete',
            'notification.view',
            'reports.view_team',
        ]);

        $employeeRole = Role::findOrCreate('Employee');
        $employeeRole->update(['description' => 'Self-service dashboard access, leave requests, and payslip viewing']);
        $employeeRole->givePermissionTo([
            'attendance.create',
            'attendance.view_self',
            'attendance.correct',
            'attendance.correction.request',
            'leave.view_self',
            'leave.create',
            'leave.cancel',
            'holiday.view',
            'announcements.view',
            'announcement.view',
            'notification.view',
            'payroll.payslip.view_self',
        ]);
    }
}
