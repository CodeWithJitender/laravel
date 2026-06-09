<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentUserRepository::class
        );

        $this->app->bind(
            \App\Repositories\LocationRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentLocationRepository::class
        );

        $this->app->bind(
            \App\Repositories\ShiftRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentShiftRepository::class
        );

        $this->app->bind(
            \App\Repositories\DepartmentRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentDepartmentRepository::class
        );

        $this->app->bind(
            \App\Repositories\DesignationRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentDesignationRepository::class
        );

        $this->app->bind(
            \App\Repositories\OfficeTimingRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentOfficeTimingRepository::class
        );

        $this->app->bind(
            \App\Repositories\AttendanceRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentAttendanceRepository::class
        );

        $this->app->bind(
            \App\Repositories\AttendanceCorrectionRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentAttendanceCorrectionRepository::class
        );

        $this->app->bind(
            \App\Repositories\AttendanceMonthlySummaryRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentAttendanceMonthlySummaryRepository::class
        );

        $this->app->bind(
            \App\Repositories\LeaveRequestRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentLeaveRequestRepository::class
        );

        $this->app->bind(
            \App\Repositories\LeaveBalanceRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentLeaveBalanceRepository::class
        );

        $this->app->bind(
            \App\Repositories\LeavePolicyRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentLeavePolicyRepository::class
        );

        $this->app->bind(
            \App\Repositories\LeaveTypeRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentLeaveTypeRepository::class
        );

        $this->app->bind(
            \App\Repositories\NotificationRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentNotificationRepository::class
        );

        $this->app->bind(
            \App\Repositories\AnnouncementRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentAnnouncementRepository::class
        );

        $this->app->bind(
            \App\Repositories\HolidayRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentHolidayRepository::class
        );



        $this->app->bind(
            \App\Repositories\PayrollRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentPayrollRepository::class
        );

        $this->app->bind(
            \App\Repositories\ReportRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentReportRepository::class
        );

        $this->app->singleton('settings', function () {
            return new \App\Services\SettingsService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\AttendanceCorrection::class,
            \App\Policies\CorrectionPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\LeaveRequest::class,
            \App\Policies\LeaveRequestPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Announcement::class,
            \App\Policies\AnnouncementPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Notification::class,
            \App\Policies\NotificationPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Holiday::class,
            \App\Policies\HolidayPolicy::class
        );



        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\ReportDefinition::class,
            \App\Policies\ReportPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\SavedReport::class,
            \App\Policies\ReportPolicy::class
        );

        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\ScheduledReport::class,
            \App\Policies\ReportPolicy::class
        );

        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\NotificationDispatcherListener::class);
        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\AuthEventsListener::class);

        // Register Audit Observers
        \App\Models\User::observe(\App\Observers\AuditModelObserver::class);
        \App\Models\EmployeeDetail::observe(\App\Observers\AuditModelObserver::class);
        \App\Models\Department::observe(\App\Observers\AuditModelObserver::class);
        \App\Models\Designation::observe(\App\Observers\AuditModelObserver::class);
        \App\Models\Location::observe(\App\Observers\AuditModelObserver::class);
        \App\Models\Shift::observe(\App\Observers\AuditModelObserver::class);
        \App\Models\LeaveRequest::observe(\App\Observers\AuditModelObserver::class);
        \App\Models\Attendance::observe(\App\Observers\AuditModelObserver::class);
        \App\Models\PayrollRun::observe(\App\Observers\AuditModelObserver::class);
        \App\Models\Payslip::observe(\App\Observers\AuditModelObserver::class);
    }
}
