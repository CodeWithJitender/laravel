<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payslip;
use App\Models\EmployeeDetail;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get central KPIs for the Analytics Dashboard.
     */
    public function getKpis(User $user): array
    {
        // Enforce basic scope constraints
        $scopeQuery = User::where('status', 'active');
        if ($user->hasRole('Manager')) {
            $scopeQuery->whereHas('employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                  ->orWhere('department_id', $user->employeeDetail->department_id ?? 0);
            });
        } elseif (!$user->hasRole('Admin')) {
            $scopeQuery->where('id', $user->id);
        }

        // 1. Total Active Headcount
        $headcount = $scopeQuery->count();

        // 2. Attrition Rate (last 12 months exits / headcount)
        $twelveMonthsAgo = now()->subYear();
        $exitsQuery = EmployeeDetail::whereNotNull('exit_date')
            ->where('exit_date', '>=', $twelveMonthsAgo->toDateString());
        if ($user->hasRole('Manager')) {
            $exitsQuery->where(function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                  ->orWhere('department_id', $user->employeeDetail->department_id ?? 0);
            });
        } elseif (!$user->hasRole('Admin')) {
            $exitsQuery->where('user_id', $user->id);
        }
        $exitsCount = $exitsQuery->count();
        $attritionRate = $headcount > 0 ? round(($exitsCount / $headcount) * 100, 2) : 0;

        // 3. Average Attendance rate for current month
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        
        $attendanceQuery = Attendance::whereBetween('attendance_date', [$startOfMonth, $endOfMonth]);
        if ($user->hasRole('Manager')) {
            $attendanceQuery->whereHas('employee.employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                  ->orWhere('department_id', $user->employeeDetail->department_id ?? 0);
            });
        } elseif (!$user->hasRole('Admin')) {
            $attendanceQuery->where('user_id', $user->id);
        }

        $totalAttendanceRecords = $attendanceQuery->count();
        $presentRecords = (clone $attendanceQuery)->where('attendance_status', 'Present')->count();
        $attendanceRate = $totalAttendanceRecords > 0 ? round(($presentRecords / $totalAttendanceRecords) * 100, 2) : 0;

        // 4. Monthly Payroll cost
        $payrollQuery = Payslip::whereNotNull('published_at');
        if ($user->hasRole('Manager')) {
            $payrollQuery->whereHas('employee.employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                  ->orWhere('department_id', $user->employeeDetail->department_id ?? 0);
            });
        } elseif (!$user->hasRole('Admin')) {
            $payrollQuery->where('employee_id', $user->id);
        }
        // Grab last published month's total cost
        $totalPayrollCost = $payrollQuery->join('payroll_run_employees', 'payslips.payroll_run_employee_id', '=', 'payroll_run_employees.id')
            ->sum('payroll_run_employees.net_salary');

        // 5. Leave utilization (Pending approvals vs Total leaves)
        $leaveQuery = LeaveRequest::query();
        if ($user->hasRole('Manager')) {
            $leaveQuery->whereHas('employee.employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id)
                  ->orWhere('department_id', $user->employeeDetail->department_id ?? 0);
            });
        } elseif (!$user->hasRole('Admin')) {
            $leaveQuery->where('employee_id', $user->id);
        }
        $pendingLeaves = (clone $leaveQuery)->where('status', 'pending')->count();
        $approvedLeaves = (clone $leaveQuery)->where('status', 'approved')->count();

        return [
            'headcount' => $headcount,
            'attrition_rate' => $attritionRate,
            'attendance_rate' => $attendanceRate,
            'total_payroll_cost' => round($totalPayrollCost, 2),
            'pending_leaves_count' => $pendingLeaves,
            'approved_leaves_count' => $approvedLeaves,
        ];
    }

    /**
     * Get monthly payroll costs for the last 6 months.
     */
    public function getPayrollCostTrend(User $user, int $monthsCount = 6): array
    {
        $trends = [];
        for ($i = $monthsCount - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            $query = Payslip::whereNotNull('published_at')
                ->whereHas('payrollRunEmployee.payrollRun', function ($q) use ($month, $year) {
                    $q->where('run_month', $month)
                      ->where('run_year', $year);
                });

            if ($user->hasRole('Manager')) {
                $query->whereHas('employee.employeeDetail', function ($q) use ($user) {
                    $q->where('manager_id', $user->id)
                      ->orWhere('department_id', $user->employeeDetail->department_id ?? 0);
                });
            } elseif (!$user->hasRole('Admin')) {
                $query->where('employee_id', $user->id);
            }

            $totalNet = $query->join('payroll_run_employees', 'payslips.payroll_run_employee_id', '=', 'payroll_run_employees.id')
                ->sum('payroll_run_employees.net_salary');

            $trends[] = [
                'label' => $date->format('M Y'),
                'value' => round($totalNet, 2)
            ];
        }

        return $trends;
    }

    /**
     * Get attendance status rate trends.
     */
    public function getAttendanceTrend(User $user, int $daysCount = 7): array
    {
        $trends = [];
        for ($i = $daysCount - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString();

            $query = Attendance::where('attendance_date', $dateString);
            if ($user->hasRole('Manager')) {
                $query->whereHas('employee.employeeDetail', function ($q) use ($user) {
                    $q->where('manager_id', $user->id)
                      ->orWhere('department_id', $user->employeeDetail->department_id ?? 0);
                });
            } elseif (!$user->hasRole('Admin')) {
                $query->where('user_id', $user->id);
            }

            $present = (clone $query)->where('attendance_status', 'Present')->count();
            $absent = (clone $query)->where('attendance_status', 'Absent')->count();
            $late = (clone $query)->where('attendance_status', 'Late')->count();

            $trends[] = [
                'date' => $date->format('d M'),
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
            ];
        }

        return $trends;
    }

    /**
     * Get Headcount Growth Trend.
     */
    public function getHeadcountGrowth(User $user, int $monthsCount = 6): array
    {
        $trends = [];
        for ($i = $monthsCount - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i)->endOfMonth();
            $dateString = $date->toDateString();

            $query = User::where('status', 'active')
                ->whereHas('employeeDetail', function ($q) use ($dateString) {
                    $q->where('joining_date', '<=', $dateString);
                });

            if ($user->hasRole('Manager')) {
                $query->whereHas('employeeDetail', function ($q) use ($user) {
                    $q->where('manager_id', $user->id)
                      ->orWhere('department_id', $user->employeeDetail->department_id ?? 0);
                });
            } elseif (!$user->hasRole('Admin')) {
                $query->where('id', $user->id);
            }

            $trends[] = [
                'label' => $date->format('M Y'),
                'headcount' => $query->count()
            ];
        }

        return $trends;
    }
}
