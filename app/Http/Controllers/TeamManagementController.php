<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Location;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeamManagementController extends Controller
{

    /**
     * Display listing of direct reports.
     */
    public function members(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search', '');
        $departmentId = $request->input('department_id', '');
        $locationId = $request->input('location_id', '');

        $query = User::with(['employeeDetail.department', 'employeeDetail.designation', 'employeeDetail.location'])
            ->where('status', 'active');

        // Scope to direct reports if user is a Manager
        if ($user->hasRole('Manager')) {
            $query->whereHas('employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('employeeDetail', function ($detail) use ($search) {
                      $detail->where('employee_code', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($departmentId) {
            $query->whereHas('employeeDetail', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($locationId) {
            $query->whereHas('employeeDetail', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        $members = $query->paginate(15)->withQueryString();

        $departments = Department::where('status', 'active')->orderBy('department_name')->get();
        $locations = Location::where('status', 'active')->orderBy('location_name')->get();

        return view('team.members', compact('members', 'departments', 'locations', 'search', 'departmentId', 'locationId'));
    }

    /**
     * Show a reporting employee's profile.
     */
    public function memberProfile($id)
    {
        $user = auth()->user();
        $employee = User::with([
            'employeeDetail.department', 
            'employeeDetail.designation', 
            'employeeDetail.location', 
            'employeeDetail.shift',
            'employeeDetail.manager',
            'roles',
            'documents',
        ])->findOrFail($id);

        // Security check: Manager can only view their direct reports
        if (!$user->hasRole('Admin') && $employee->employeeDetail?->manager_id !== $user->id) {
            abort(403, 'You are not authorized to view this employee.');
        }

        $activities = ActivityLog::where('user_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('team.profile', compact('employee', 'activities'));
    }

    /**
     * Display hierarchical reporting tree structure.
     */
    public function structure()
    {
        $user = auth()->user();

        // Get manager (root)
        $rootUser = $user;
        if ($user->hasRole('Manager') && $user->employeeDetail?->manager) {
            $rootUser = $user->employeeDetail->manager;
        }

        // Fetch direct reports of the root
        $hierarchy = User::with(['employeeDetail.designation', 'employeeDetail.department'])
            ->where('status', 'active')
            ->whereHas('employeeDetail', function ($q) use ($rootUser) {
                $q->where('manager_id', $rootUser->id);
            })
            ->get();

        // Recursively build children list for hierarchy tree
        $hierarchyData = [];
        foreach ($hierarchy as $report) {
            $children = User::with(['employeeDetail.designation', 'employeeDetail.department'])
                ->where('status', 'active')
                ->whereHas('employeeDetail', function ($q) use ($report) {
                    $q->where('manager_id', $report->id);
                })
                ->get();
            
            $hierarchyData[] = [
                'user' => $report,
                'children' => $children
            ];
        }

        return view('team.structure', compact('rootUser', 'hierarchyData'));
    }

    /**
     * Team leave calendar view.
     */
    public function calendar(Request $request)
    {
        $user = auth()->user();
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        // Query leaves of reporting employees
        $leavesQuery = LeaveRequest::with(['employee', 'leaveType'])
            ->whereIn('status', ['approved', 'pending'])
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                  ->orWhereBetween('end_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            });

        if ($user->hasRole('Manager')) {
            $leavesQuery->whereHas('employee.employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        $leaves = $leavesQuery->get();

        // Query location holidays
        $locationId = $user->employeeDetail?->location_id;
        $holidays = Holiday::whereBetween('holiday_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->where(function ($q) use ($locationId) {
                if ($locationId) {
                    $q->whereHas('locations', fn($l) => $l->where('locations.id', $locationId))
                      ->orWhereDoesntHave('locations');
                }
            })
            ->get();

        return view('team.calendar', compact('leaves', 'holidays', 'month', 'year'));
    }

    /**
     * Display team summary analytics and compliance reports.
     */
    public function reports(Request $request)
    {
        $user = auth()->user();
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->toDateString();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        // Get list of direct reports user IDs
        $reportIdsQuery = User::where('status', 'active');
        if ($user->hasRole('Manager')) {
            $reportIdsQuery->whereHas('employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }
        $reportIds = $reportIdsQuery->pluck('id')->toArray();

        // 1. Attendance late clock-ins report
        $lateReport = Attendance::with('user.employeeDetail.department')
            ->whereIn('user_id', $reportIds)
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->where('late_minutes', '>', 0)
            ->orderBy('attendance_date', 'desc')
            ->get();

        // 2. Leave Summary count per employee
        $leaveSummary = LeaveRequest::select('employee_id', DB::raw('SUM(total_days) as total_days_taken'))
            ->whereIn('employee_id', $reportIds)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->groupBy('employee_id')
            ->with('employee')
            ->get();

        // 3. Average working hours per employee
        $hoursSummary = Attendance::select('user_id', DB::raw('AVG(worked_hours) as avg_hours'))
            ->whereIn('user_id', $reportIds)
            ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->where('attendance_status', '!=', 'Absent')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        return view('team.reports', compact('lateReport', 'leaveSummary', 'hoursSummary', 'month', 'year'));
    }
}
