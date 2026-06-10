<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/run-migrations', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return 'Migrations run successfully! Output: <pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return 'Error running migrations: ' . $e->getMessage();
    }
});

Route::get('/run-seed', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return 'Database seeding run successfully! Output: <pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return 'Error running seed: ' . $e->getMessage();
    }
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'status.check', 'maintenance.check', 'session.timeout'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/change-password', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Sessions
    Route::get('/sessions', [\App\Http\Controllers\SessionController::class, 'index'])->name('sessions.index');
    Route::delete('/sessions/{id}', [\App\Http\Controllers\SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::post('/sessions/clear-all', [\App\Http\Controllers\SessionController::class, 'clearAll'])->name('sessions.clear_all');
    
    // Roles & Permissions Management
    Route::middleware('role:Admin')->group(function () {
        Route::resource('/roles', \App\Http\Controllers\RoleController::class);
        Route::get('/permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions/sync', [\App\Http\Controllers\PermissionController::class, 'syncMatrix']);
    });

    // Employee Management
    Route::resource('/employees', \App\Http\Controllers\EmployeeController::class);

    // Organization Setup
    Route::resource('/locations', \App\Http\Controllers\LocationController::class);
    Route::resource('/shifts', \App\Http\Controllers\ShiftController::class);
    Route::resource('/departments', \App\Http\Controllers\DepartmentController::class);
    Route::resource('/designations', \App\Http\Controllers\DesignationController::class);
    Route::get('/office-timings', [\App\Http\Controllers\OfficeTimingController::class, 'show'])->name('office-timings.show');
    Route::put('/office-timings', [\App\Http\Controllers\OfficeTimingController::class, 'update'])->name('office-timings.update');
    Route::get('/org-structure', [\App\Http\Controllers\OrgStructureController::class, 'index'])->name('org-structure.index');

    // Attendance Management
    Route::get('/timecard', [\App\Http\Controllers\AttendanceClockController::class, 'showTimecard'])->name('attendance.punch');
    Route::post('/attendance/clock-in', [\App\Http\Controllers\AttendanceClockController::class, 'clockIn'])->name('attendance.clock_in');
    Route::post('/attendance/clock-out', [\App\Http\Controllers\AttendanceClockController::class, 'clockOut'])->name('attendance.clock_out');
    Route::get('/attendance/my-history', [\App\Http\Controllers\AttendanceConsoleController::class, 'myHistory'])->name('attendance.my_history');
    Route::get('/attendance/reports', [\App\Http\Controllers\AttendanceReportController::class, 'index'])->name('attendance.reports.index');
    Route::get('/attendance/reports/generate', [\App\Http\Controllers\AttendanceReportController::class, 'generate'])->name('attendance.reports.generate');
    
    // Correction requests
    Route::resource('/attendance/corrections', \App\Http\Controllers\AttendanceCorrectionController::class)->names([
        'index' => 'attendance.corrections.index',
        'create' => 'attendance.corrections.create',
        'store' => 'attendance.corrections.store',
        'show' => 'attendance.corrections.show',
        'edit' => 'attendance.corrections.edit',
        'update' => 'attendance.corrections.update',
        'destroy' => 'attendance.corrections.destroy',
    ]);
    Route::put('/attendance/corrections/{id}/review', [\App\Http\Controllers\AttendanceCorrectionController::class, 'review'])->name('attendance.corrections.review');

    // Daily punch sheet resource
    Route::resource('/attendance', \App\Http\Controllers\AttendanceConsoleController::class)->names([
        'index' => 'attendance.index',
        'create' => 'attendance.create',
        'store' => 'attendance.store',
        'show' => 'attendance.show',
        'edit' => 'attendance.edit',
        'update' => 'attendance.update',
        'destroy' => 'attendance.destroy',
    ]);

    // Leave Management Routes
    Route::resource('/leave', \App\Http\Controllers\LeaveRequestController::class)->names([
        'index' => 'leave.index',
        'create' => 'leave.create',
        'store' => 'leave.store',
        'show' => 'leave.show',
        'edit' => 'leave.edit',
        'update' => 'leave.update',
        'destroy' => 'leave.destroy',
    ]);
    Route::put('/leave/{id}/review', [\App\Http\Controllers\LeaveRequestController::class, 'review'])->name('leave.review');
    Route::put('/leave/{id}/cancel', [\App\Http\Controllers\LeaveRequestController::class, 'cancel'])->name('leave.cancel');

    // Notification & Communication Engine Routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::post('/notifications/{id}/archive', [\App\Http\Controllers\NotificationController::class, 'archive'])->name('notifications.archive');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Announcements
    Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements/categories', [\App\Http\Controllers\AnnouncementController::class, 'storeCategory'])->name('announcements.categories.store');
    Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::put('/announcements/{id}', [\App\Http\Controllers\AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{id}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::post('/announcements/{id}/publish', [\App\Http\Controllers\AnnouncementController::class, 'publish'])->name('announcements.publish');
    Route::post('/announcements/{id}/read', [\App\Http\Controllers\AnnouncementController::class, 'markRead'])->name('announcements.read');

    // Notification Templates & Logs (Admin / Authorized)
    Route::get('/notifications/templates', [\App\Http\Controllers\NotificationTemplateController::class, 'index'])->name('notification-templates.index');
    Route::get('/notifications/templates/{id}/edit', [\App\Http\Controllers\NotificationTemplateController::class, 'edit'])->name('notification-templates.edit');
    Route::put('/notifications/templates/{id}', [\App\Http\Controllers\NotificationTemplateController::class, 'update'])->name('notification-templates.update');
    Route::get('/notifications/logs', [\App\Http\Controllers\NotificationTemplateController::class, 'logs'])->name('notification-logs.index');

    // Holiday Management Routes
    Route::get('/holidays', [\App\Http\Controllers\HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [\App\Http\Controllers\HolidayController::class, 'store'])->name('holidays.store');
    Route::put('/holidays/{id}', [\App\Http\Controllers\HolidayController::class, 'update'])->name('holidays.update');
    Route::delete('/holidays/{id}', [\App\Http\Controllers\HolidayController::class, 'destroy'])->name('holidays.destroy');
    Route::post('/holidays/{id}/publish', [\App\Http\Controllers\HolidayController::class, 'publish'])->name('holidays.publish');
    Route::get('/holiday-calendar', [\App\Http\Controllers\HolidayController::class, 'calendar'])->name('holidays.calendar');
    Route::get('/holiday-reports', [\App\Http\Controllers\HolidayController::class, 'reports'])->name('holidays.reports');



    // Admin Custom Configurations
    Route::middleware('role:Admin')->group(function () {
        Route::resource('/leave-types', \App\Http\Controllers\LeaveTypeController::class)->names([
            'index' => 'leave-types.index',
            'store' => 'leave-types.store',
            'update' => 'leave-types.update',
            'destroy' => 'leave-types.destroy',
        ]);
        Route::resource('/leave-policies', \App\Http\Controllers\LeavePolicyController::class)->names([
            'index' => 'leave-policies.index',
            'store' => 'leave-policies.store',
            'update' => 'leave-policies.update',
            'destroy' => 'leave-policies.destroy',
        ]);
    });

    // Payroll Management Routes
    Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/create', [\App\Http\Controllers\PayrollController::class, 'create'])->name('payroll.create');
    Route::post('/payroll', [\App\Http\Controllers\PayrollController::class, 'store'])->name('payroll.store');
    Route::get('/payroll/{id}', [\App\Http\Controllers\PayrollController::class, 'show'])->name('payroll.show');
    Route::post('/payroll/{id}/approve', [\App\Http\Controllers\PayrollController::class, 'approve'])->name('payroll.approve');
    Route::post('/payroll/{id}/publish', [\App\Http\Controllers\PayrollController::class, 'publish'])->name('payroll.publish');

    // Salary Structure & Component Routes
    Route::get('/salary-structures', [\App\Http\Controllers\SalaryStructureController::class, 'index'])->name('salary-structures.index');
    Route::post('/salary-structures', [\App\Http\Controllers\SalaryStructureController::class, 'store'])->name('salary-structures.store');
    Route::put('/salary-structures/{id}/components', [\App\Http\Controllers\SalaryStructureController::class, 'updateComponents'])->name('salary-structures.components.update');
    Route::get('/salary-structures/assign', [\App\Http\Controllers\SalaryStructureController::class, 'assignForm'])->name('salary-structures.assign');
    Route::post('/salary-structures/assign', [\App\Http\Controllers\SalaryStructureController::class, 'storeAssignment'])->name('salary-structures.assign.store');

    // Salary Revisions
    Route::get('/salary-revisions', [\App\Http\Controllers\SalaryRevisionController::class, 'index'])->name('salary-revisions.index');
    Route::post('/salary-revisions', [\App\Http\Controllers\SalaryRevisionController::class, 'store'])->name('salary-revisions.store');
    Route::post('/salary-revisions/{id}/approve', [\App\Http\Controllers\SalaryRevisionController::class, 'approve'])->name('salary-revisions.approve');

    // Payslips Self-Service & Directory
    Route::get('/payslips', [\App\Http\Controllers\PayslipController::class, 'index'])->name('payslips.index');
    Route::get('/my-payslips', [\App\Http\Controllers\PayslipController::class, 'index'])->name('payslips.my_payslips');
    Route::get('/payslips/{id}', [\App\Http\Controllers\PayslipController::class, 'show'])->name('payslips.show');
    Route::get('/payslips/{id}/download', [\App\Http\Controllers\PayslipController::class, 'download'])->name('payslips.download');

    // Reports Engine Routes
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{uuid}', [\App\Http\Controllers\ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{uuid}/preview', [\App\Http\Controllers\ReportController::class, 'preview'])->name('reports.preview');
    Route::post('/reports/generate', [\App\Http\Controllers\ReportController::class, 'generate'])->name('reports.generate');
    Route::post('/reports/template', [\App\Http\Controllers\ReportController::class, 'saveTemplate'])->name('reports.template.save');
    Route::post('/reports/schedule', [\App\Http\Controllers\ReportController::class, 'schedule'])->name('reports.schedule');
    Route::post('/reports/{uuid}/favorite', [\App\Http\Controllers\ReportController::class, 'toggleFavorite'])->name('reports.favorite.toggle');
    Route::get('/reports/exports/{uuid}/download', [\App\Http\Controllers\ReportController::class, 'download'])->name('reports.download');

    // Team Management Routes
    Route::middleware('role:Admin|Manager')->group(function () {
        Route::get('/team-members', [\App\Http\Controllers\TeamManagementController::class, 'members'])->name('team.members');
        Route::get('/team-members/structure', [\App\Http\Controllers\TeamManagementController::class, 'structure'])->name('team.structure');
        Route::get('/team-members/{id}', [\App\Http\Controllers\TeamManagementController::class, 'memberProfile'])->name('team.member_profile');
        Route::get('/team-calendar', [\App\Http\Controllers\TeamManagementController::class, 'calendar'])->name('team.calendar');
        Route::get('/team-reports', [\App\Http\Controllers\TeamManagementController::class, 'reports'])->name('team.reports');
    });

    // Analytics Engine Routes
    Route::get('/analytics/kpis', [\App\Http\Controllers\AnalyticsController::class, 'kpiSummary'])->name('analytics.kpis');
    Route::get('/analytics/trends/payroll', [\App\Http\Controllers\AnalyticsController::class, 'payrollTrend'])->name('analytics.trends.payroll');
    Route::get('/analytics/trends/attendance', [\App\Http\Controllers\AnalyticsController::class, 'attendanceTrend'])->name('analytics.trends.attendance');
    Route::get('/analytics/trends/headcount', [\App\Http\Controllers\AnalyticsController::class, 'headcountTrend'])->name('analytics.trends.headcount');

    // Settings Engine Routes
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/{group}', [\App\Http\Controllers\SettingsController::class, 'getSettings'])->name('settings.get');
    Route::put('/settings/{group}', [\App\Http\Controllers\SettingsController::class, 'updateSettings'])->name('settings.update');
    Route::get('/feature-flags', [\App\Http\Controllers\SettingsController::class, 'getFeatureFlags'])->name('settings.flags.get');
    Route::put('/feature-flags', [\App\Http\Controllers\SettingsController::class, 'updateFeatureFlag'])->name('settings.flags.update');

    // Audit and Governance Routes
    Route::get('/audit-logs', [\App\Http\Controllers\AuditController::class, 'auditLogs'])->name('audit.logs');
    Route::get('/activity-logs', [\App\Http\Controllers\AuditController::class, 'activityLogs'])->name('activity.logs');
    Route::get('/login-history', [\App\Http\Controllers\AuditController::class, 'loginHistory'])->name('login.history');

    // Self-Service Profile Routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/emergency-contacts', [\App\Http\Controllers\ProfileController::class, 'storeEmergencyContact'])->name('profile.emergency-contacts.store');
    Route::put('/profile/emergency-contacts/{id}', [\App\Http\Controllers\ProfileController::class, 'updateEmergencyContact'])->name('profile.emergency-contacts.update');
    Route::delete('/profile/emergency-contacts/{id}', [\App\Http\Controllers\ProfileController::class, 'destroyEmergencyContact'])->name('profile.emergency-contacts.destroy');
});


