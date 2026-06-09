<?php

namespace App\Listeners;

use App\Services\NotificationService;
use App\Events\LeaveApplied;
use App\Events\LeaveApproved;
use App\Events\LeaveRejected;
use App\Events\AttendanceCorrectionSubmitted;
use App\Events\AttendanceCorrectionApproved;
use App\Events\EmployeeCreated;
use App\Events\HolidayPublished;
use App\Events\HolidayReminderTriggered;

use App\Events\PayrollPublished;
use App\Events\SalaryRevised;
use Illuminate\Events\Dispatcher;
use Carbon\Carbon;

class NotificationDispatcherListener
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle Leave Request Submitted event.
     */
    public function handleLeaveApplied(LeaveApplied $event): void
    {
        $leaveRequest = $event->leaveRequest;
        $leaveRequest->loadMissing(['employee.employeeDetail', 'leaveType']);

        $employee = $leaveRequest->employee;
        $detail = $employee->employeeDetail;
        $managerId = $detail ? $detail->manager_id : null;

        if ($managerId) {
            $data = [
                'employee_name' => $employee->name,
                'leave_type' => $leaveRequest->leaveType->name,
                'start_date' => $leaveRequest->start_date->toDateString(),
                'end_date' => $leaveRequest->end_date->toDateString(),
                'total_days' => $leaveRequest->total_days,
                'reason' => $leaveRequest->reason,
            ];

            $this->notificationService->sendFromTemplate(
                'leave_request_submitted', 
                $data, 
                'single', 
                $managerId, 
                $employee
            );
        }
    }

    /**
     * Handle Leave Request Approved event.
     */
    public function handleLeaveApproved(LeaveApproved $event): void
    {
        $leaveRequest = $event->leaveRequest;
        $leaveRequest->loadMissing(['employee', 'leaveType', 'approver']);

        $employee = $leaveRequest->employee;
        $approverName = $leaveRequest->approver ? $leaveRequest->approver->name : 'Manager';
        
        $latestHistory = $leaveRequest->statusHistory()->orderBy('id', 'desc')->first();
        $remarks = $latestHistory ? $latestHistory->remarks : 'Approved';

        $data = [
            'leave_type' => $leaveRequest->leaveType->name,
            'start_date' => $leaveRequest->start_date->toDateString(),
            'end_date' => $leaveRequest->end_date->toDateString(),
            'approver_name' => $approverName,
            'remarks' => $remarks,
        ];

        $this->notificationService->sendFromTemplate(
            'leave_request_approved', 
            $data, 
            'single', 
            $leaveRequest->employee_id, 
            $leaveRequest->approver
        );
    }

    /**
     * Handle Leave Request Rejected event.
     */
    public function handleLeaveRejected(LeaveRejected $event): void
    {
        $leaveRequest = $event->leaveRequest;
        $leaveRequest->loadMissing(['employee', 'leaveType']);

        $employee = $leaveRequest->employee;
        $latestHistory = $leaveRequest->statusHistory()->orderBy('id', 'desc')->first();
        $remarks = $latestHistory ? $latestHistory->remarks : 'Rejected';
        
        // Find approver name
        $approverName = 'Manager';
        if ($latestHistory && $latestHistory->user) {
            $approverName = $latestHistory->user->name;
        } elseif ($leaveRequest->approved_by) {
            $leaveRequest->loadMissing('approver');
            $approverName = $leaveRequest->approver ? $leaveRequest->approver->name : 'Manager';
        }

        $data = [
            'leave_type' => $leaveRequest->leaveType->name,
            'start_date' => $leaveRequest->start_date->toDateString(),
            'end_date' => $leaveRequest->end_date->toDateString(),
            'approver_name' => $approverName,
            'remarks' => $remarks,
        ];

        $this->notificationService->sendFromTemplate(
            'leave_request_rejected', 
            $data, 
            'single', 
            $leaveRequest->employee_id
        );
    }

    /**
     * Handle Attendance Correction Submitted event.
     */
    public function handleCorrectionSubmitted(AttendanceCorrectionSubmitted $event): void
    {
        $correction = $event->correction;
        $correction->loadMissing(['user.employeeDetail']);

        $employee = $correction->user;
        $detail = $employee->employeeDetail;
        $managerId = $detail ? $detail->manager_id : null;

        if ($managerId) {
            $data = [
                'employee_name' => $employee->name,
                'requested_date' => $correction->requested_date->toDateString(),
                'requested_clock_in' => $correction->requested_clock_in->format('H:i'),
                'requested_clock_out' => $correction->requested_clock_out->format('H:i'),
                'reason' => $correction->reason,
            ];

            $this->notificationService->sendFromTemplate(
                'attendance_correction_submitted', 
                $data, 
                'single', 
                $managerId, 
                $employee
            );
        }
    }

    /**
     * Handle Attendance Correction Approved event.
     */
    public function handleCorrectionApproved(AttendanceCorrectionApproved $event): void
    {
        $correction = $event->correction;
        $correction->loadMissing(['user', 'approvedBy']);

        $employee = $correction->user;
        $approverName = $correction->approvedBy ? $correction->approvedBy->name : 'Manager';

        $data = [
            'requested_date' => $correction->requested_date->toDateString(),
            'requested_clock_in' => $correction->requested_clock_in->format('H:i'),
            'requested_clock_out' => $correction->requested_clock_out->format('H:i'),
            'approver_name' => $approverName,
        ];

        $this->notificationService->sendFromTemplate(
            'attendance_correction_approved', 
            $data, 
            'single', 
            $correction->user_id, 
            $correction->approvedBy
        );
    }

    /**
     * Handle Employee Created event.
     */
    public function handleEmployeeCreated(EmployeeCreated $event): void
    {
        $user = $event->user;

        $data = [
            'employee_name' => $user->name,
            'employee_email' => $user->email,
        ];

        $this->notificationService->sendFromTemplate(
            'employee_welcome', 
            $data, 
            'single', 
            $user->id
        );
    }



    /**
     * Handle Holiday Published event.
     */
    public function handleHolidayPublished(HolidayPublished $event): void
    {
        $holiday = $event->holiday;
        $holiday->loadMissing('locations');

        $data = [
            'holiday_name' => $holiday->holiday_name,
            'holiday_date' => $holiday->holiday_date->toDateString(),
        ];

        // Find eligible users
        $query = \App\Models\User::where('status', 'active');
        
        if ($holiday->locations->isNotEmpty()) {
            $locationIds = $holiday->locations->pluck('id')->toArray();
            $query->whereHas('employeeDetail', function ($q) use ($locationIds) {
                $q->whereIn('location_id', $locationIds);
            });
        }

        $userIds = $query->pluck('id')->toArray();

        foreach ($userIds as $userId) {
            $this->notificationService->sendFromTemplate(
                'holiday_published', 
                $data, 
                'single', 
                $userId
            );
        }
    }

    /**
     * Handle Holiday Reminder Triggered event.
     */
    public function handleHolidayReminderTriggered(HolidayReminderTriggered $event): void
    {
        $holiday = $event->holiday;
        $holiday->loadMissing('locations');

        $data = [
            'holiday_name' => $holiday->holiday_name,
            'holiday_date' => $holiday->holiday_date->toDateString(),
            'days_before' => $event->daysBefore,
        ];

        // Find eligible users
        $query = \App\Models\User::where('status', 'active');
        
        if ($holiday->locations->isNotEmpty()) {
            $locationIds = $holiday->locations->pluck('id')->toArray();
            $query->whereHas('employeeDetail', function ($q) use ($locationIds) {
                $q->whereIn('location_id', $locationIds);
            });
        }

        $userIds = $query->pluck('id')->toArray();

        foreach ($userIds as $userId) {
            $this->notificationService->sendFromTemplate(
                'holiday_reminder', 
                $data, 
                'single', 
                $userId
            );
        }
    }

    /**
     * Handle Payroll Published event.
     */
    public function handlePayrollPublished(PayrollPublished $event): void
    {
        $payrollRun = $event->payrollRun;
        $payrollRun->loadMissing('employees.employee');
        foreach ($payrollRun->employees as $runEmployee) {
            $employee = $runEmployee->employee;
            if (!$employee) continue;
            
            $data = [
                'employee_name' => $employee->name,
                'month_name' => Carbon::createFromDate($payrollRun->run_year, $payrollRun->run_month, 1)->format('F'),
                'year' => $payrollRun->run_year,
                'net_salary' => number_format($runEmployee->net_salary, 2),
            ];

            $this->notificationService->sendFromTemplate(
                'payroll_published', 
                $data, 
                'single', 
                $employee->id
            );
        }
    }

    /**
     * Handle Salary Revised event.
     */
    public function handleSalaryRevised(SalaryRevised $event): void
    {
        $revision = $event->salaryRevision;
        $revision->loadMissing('employee');
        $employee = $revision->employee;
        if (!$employee) return;

        $data = [
            'employee_name' => $employee->name,
            'new_gross' => number_format($revision->new_gross_salary, 2),
            'effective_from' => Carbon::parse($revision->effective_from)->toDateString(),
            'reason' => $revision->reason ?? 'Annual increment / revision',
        ];

        $this->notificationService->sendFromTemplate(
            'salary_revised', 
            $data, 
            'single', 
            $employee->id
        );
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            LeaveApplied::class => 'handleLeaveApplied',
            LeaveApproved::class => 'handleLeaveApproved',
            LeaveRejected::class => 'handleLeaveRejected',
            AttendanceCorrectionSubmitted::class => 'handleCorrectionSubmitted',
            AttendanceCorrectionApproved::class => 'handleCorrectionApproved',
            EmployeeCreated::class => 'handleEmployeeCreated',
            HolidayPublished::class => 'handleHolidayPublished',
            HolidayReminderTriggered::class => 'handleHolidayReminderTriggered',

            PayrollPublished::class => 'handlePayrollPublished',
            SalaryRevised::class => 'handleSalaryRevised',
        ];
    }
}
