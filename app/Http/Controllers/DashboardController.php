<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Holiday;
use App\Models\Payslip;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin')) {
            $totalEmployees = User::count();
            $activeEmployees = User::where('status', 'active')->count();
            
            $today = Carbon::today()->toDateString();
            
            $presentToday = Attendance::whereDate('attendance_date', $today)
                ->whereIn('attendance_status', ['Present', 'Late'])
                ->count();
                
            $onLeave = Attendance::whereDate('attendance_date', $today)
                ->where('attendance_status', 'On Leave')
                ->count();
                
            $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
            
            // Fetch sum of net salary for latest payroll run
            $latestPayslip = Payslip::orderBy('created_at', 'desc')->first();
            $payrollSummary = "$0.00";
            if ($latestPayslip && $latestPayslip->payrollRunEmployee) {
                $runId = $latestPayslip->payrollRunEmployee->payroll_run_id;
                $sumNet = DB::table('payroll_run_employees')
                    ->where('payroll_run_id', $runId)
                    ->sum('net_salary');
                $payrollSummary = "$" . number_format($sumNet, 2);
            }
            
            // Fetch upcoming holidays
            $dbHolidays = Holiday::where('holiday_date', '>=', $today)
                ->orderBy('holiday_date', 'asc')
                ->take(3)
                ->get();
                
            $upcomingHolidays = [];
            foreach ($dbHolidays as $holiday) {
                $upcomingHolidays[] = [
                    'name' => $holiday->holiday_name,
                    'date' => $holiday->holiday_date->format('M d, Y'),
                    'days_left' => today()->diffInDays($holiday->holiday_date, false),
                ];
            }
            
            if (empty($upcomingHolidays)) {
                $upcomingHolidays = [
                    ['name' => 'Independence Day', 'date' => 'July 4, 2026', 'days_left' => 30],
                ];
            }
            
            // Fetch recent activity logs from the newly created governance tables
            $dbActivities = ActivityLog::orderBy('created_at', 'desc')
                ->take(5)
                ->get();
                
            $activities = [];
            foreach ($dbActivities as $act) {
                $activities[] = [
                    'text' => $act->activity . ': ' . $act->description,
                    'time' => $act->created_at->diffForHumans(),
                ];
            }
            
            if (empty($activities)) {
                $activities = [
                    ['text' => 'New employee John Employee created', 'time' => '10 mins ago'],
                    ['text' => 'Regular Shift assigned to Jane Manager', 'time' => '1 hour ago'],
                    ['text' => 'System settings updated by Admin', 'time' => '2 hours ago']
                ];
            }
            
            return view('dashboard.admin', compact(
                'totalEmployees', 'activeEmployees', 'presentToday', 
                'onLeave', 'pendingLeaves', 'payrollSummary', 
                'upcomingHolidays', 'activities'
            ));
        }
        
        if ($user->hasRole('Manager')) {
            $teamSize = User::whereHas('employeeDetail', function($q) use ($user) {
                $q->where('manager_id', $user->id);
            })->count();
            
            $today = Carbon::today()->toDateString();
            
            $teamPresent = Attendance::whereDate('attendance_date', $today)
                ->whereIn('attendance_status', ['Present', 'Late'])
                ->whereHas('user.employeeDetail', function($q) use ($user) {
                    $q->where('manager_id', $user->id);
                })
                ->count();
                
            $teamOnLeave = Attendance::whereDate('attendance_date', $today)
                ->where('attendance_status', 'On Leave')
                ->whereHas('user.employeeDetail', function($q) use ($user) {
                    $q->where('manager_id', $user->id);
                })
                ->count();
                
            $pendingLeaves = LeaveRequest::where('status', 'pending')
                ->whereHas('employee.employeeDetail', function($q) use ($user) {
                    $q->where('manager_id', $user->id);
                })
                ->count();

            $pendingCorrections = \App\Models\AttendanceCorrection::where('status', 'pending')
                ->whereHas('user.employeeDetail', function($q) use ($user) {
                    $q->where('manager_id', $user->id);
                })
                ->count();

            $pendingApprovals = $pendingLeaves + $pendingCorrections;
            
            // Fetch upcoming holidays based on manager's location
            $locationId = $user->employeeDetail?->location_id;
            $dbHolidays = Holiday::where('holiday_date', '>=', $today)
                ->where(function ($query) use ($locationId) {
                    if ($locationId) {
                        $query->whereHas('locations', function ($q) use ($locationId) {
                            $q->where('locations.id', $locationId);
                        })->orWhereDoesntHave('locations');
                    }
                })
                ->orderBy('holiday_date', 'asc')
                ->take(3)
                ->get();
                
            $upcomingHolidays = [];
            foreach ($dbHolidays as $holiday) {
                $upcomingHolidays[] = [
                    'name' => $holiday->holiday_name,
                    'date' => $holiday->holiday_date->format('M d, Y'),
                    'days_left' => today()->diffInDays($holiday->holiday_date, false),
                ];
            }
            
            if (empty($upcomingHolidays)) {
                $upcomingHolidays = [
                    ['name' => 'Independence Day', 'date' => 'July 4, 2026', 'days_left' => 30]
                ];
            }
            
            return view('dashboard.manager', compact(
                'teamSize', 'teamPresent', 'teamOnLeave', 'pendingApprovals', 'upcomingHolidays'
            ));
        }
        
        // Employee Dashboard
        $employeeDetail = $user->employeeDetail;
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // Attendance stats for current month
        $attendanceSummary = [
            'present' => Attendance::where('user_id', $user->id)
                ->whereMonth('attendance_date', $now->month)
                ->whereYear('attendance_date', $now->year)
                ->whereIn('attendance_status', ['Present', 'Late'])
                ->count(),
            'late' => Attendance::where('user_id', $user->id)
                ->whereMonth('attendance_date', $now->month)
                ->whereYear('attendance_date', $now->year)
                ->where('attendance_status', 'Late')
                ->count(),
            'absent' => Attendance::where('user_id', $user->id)
                ->whereMonth('attendance_date', $now->month)
                ->whereYear('attendance_date', $now->year)
                ->where('attendance_status', 'Absent')
                ->count()
        ];
        
        // Sum leave balances
        $leaveBalances = \App\Models\LeaveBalance::where('employee_id', $user->id)->get();
        $leaveBalance = [
            'allocated' => $leaveBalances->sum('allocated_balance') + $leaveBalances->sum('carry_forward_balance'),
            'used' => $leaveBalances->sum('used_balance'),
            'remaining' => $leaveBalances->sum('remaining_balance')
        ];
        
        // Upcoming Holidays scoped by location
        $locationId = $employeeDetail?->location_id;
        $dbHolidays = Holiday::where('holiday_date', '>=', $today)
            ->where(function ($query) use ($locationId) {
                if ($locationId) {
                    $query->whereHas('locations', function ($q) use ($locationId) {
                        $q->where('locations.id', $locationId);
                    })->orWhereDoesntHave('locations');
                }
            })
            ->orderBy('holiday_date', 'asc')
            ->take(3)
            ->get();
            
        $upcomingHolidays = [];
        foreach ($dbHolidays as $holiday) {
            $upcomingHolidays[] = [
                'name' => $holiday->holiday_name,
                'date' => $holiday->holiday_date->format('M d, Y'),
                'days_left' => today()->diffInDays($holiday->holiday_date, false),
            ];
        }
        
        if (empty($upcomingHolidays)) {
            $upcomingHolidays = [
                ['name' => 'New Year', 'date' => 'Jan 01, 2027', 'days_left' => today()->diffInDays(Carbon::parse('2027-01-01'), false)]
            ];
        }
        
        // Announcements via repo
        $announcementRepo = app(\App\Repositories\AnnouncementRepositoryInterface::class);
        $announcementsCollection = $announcementRepo->getActiveAnnouncementsForUser($user, 3);
        $announcements = [];
        foreach ($announcementsCollection as $ann) {
            $announcements[] = [
                'title' => $ann->title,
                'content' => $ann->description,
                'date' => $ann->publish_at ? $ann->publish_at->format('M d, Y') : $ann->created_at->format('M d, Y')
            ];
        }
        if (empty($announcements)) {
            $announcements = [
                ['title' => 'Welcome to HRMS', 'content' => 'Browse the console to view schedules, check payslips, and apply for leaves.', 'date' => now()->format('M d, Y')]
            ];
        }
        
        // Leave History
        $dbLeaveRequests = LeaveRequest::with('leaveType')
            ->where('employee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $leaveRequests = [];
        foreach ($dbLeaveRequests as $req) {
            $leaveRequests[] = [
                'type' => $req->leaveType->name,
                'duration' => $req->start_date->format('M d') . ' - ' . $req->end_date->format('M d') . ' (' . number_format($req->total_days, 1) . ' days)',
                'status' => $req->status
            ];
        }

        // Clock session state
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->orderBy('attendance_date', 'desc')
            ->first();

        $clockedOutToday = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->whereNotNull('clock_out')
            ->exists();

        // Working hours this month
        $workingHoursSummary = Attendance::where('user_id', $user->id)
            ->whereMonth('attendance_date', $now->month)
            ->whereYear('attendance_date', $now->year)
            ->sum('worked_hours');

        // Pending leaves count
        $pendingLeaveRequestsCount = LeaveRequest::where('employee_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Latest Payslip
        $latestPayslip = Payslip::where('employee_id', $user->id)
            ->whereNotNull('published_at')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('dashboard.employee', compact(
            'attendanceSummary', 'leaveBalance', 'upcomingHolidays', 'announcements', 
            'leaveRequests', 'activeSession', 'clockedOutToday', 'workingHoursSummary', 
            'pendingLeaveRequestsCount', 'latestPayslip'
        ));
    }
}
