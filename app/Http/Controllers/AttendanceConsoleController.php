<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendanceConsoleController extends Controller
{
    public function index(Request $request)
    {
        if (Gate::denies('attendance.view')) {
            abort(403);
        }

        $search = $request->input('search', '');
        $departmentId = $request->input('department_id', '');
        $shiftId = $request->input('shift_id', '');
        $date = $request->input('date', Carbon::today()->toDateString());

        $user = auth()->user();
        $query = Attendance::with(['user.employeeDetail.department', 'user.employeeDetail.designation', 'shift'])
            ->whereDate('attendance_date', $date);

        if ($user->hasRole('Manager')) {
            $query->whereHas('user.employeeDetail', function ($q) use ($user) {
                $q->where('manager_id', $user->id);
            });
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('employeeDetail', function ($ed) use ($search) {
                      $ed->where('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($departmentId) {
            $query->whereHas('user.employeeDetail', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        }

        $attendances = $query->paginate(15);
        $departments = Department::where('status', 'active')->orderBy('department_name')->get();
        $shifts = Shift::where('status', 'active')->orderBy('shift_name')->get();

        if ($request->wantsJson()) {
            return response()->json($attendances);
        }

        return view('attendance.index', compact(
            'attendances', 'departments', 'shifts', 'search', 'departmentId', 'shiftId', 'date'
        ));
    }

    public function myHistory(Request $request)
    {
        $user = auth()->user();
        
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        // Fetch monthly summary
        $summary = \App\Models\AttendanceMonthlySummary::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($request->input('view') === 'list') {
            $query = Attendance::with('shift')
                ->where('user_id', $user->id)
                ->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year)
                ->orderBy('attendance_date', 'desc');

            $history = $query->paginate(15)->withQueryString();

            if ($request->wantsJson()) {
                return response()->json($history);
            }

            return view('attendance.monthly', compact('history', 'summary', 'month', 'year'));
        }

        $attendances = Attendance::with('shift')
            ->where('user_id', $user->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get()
            ->keyBy(function ($item) {
                return $item->attendance_date->toDateString();
            });

        // Generate calendar dates
        $startOfMonth = Carbon::create($year, $month, 1);
        $daysInMonth = $startOfMonth->daysInMonth;
        
        $calendar = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dayDate = Carbon::create($year, $month, $i)->toDateString();
            $calendar[$dayDate] = $attendances->get($dayDate) ?? null;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'calendar' => $calendar,
                'summary' => $summary
            ]);
        }

        return view('attendance.monthly', compact('calendar', 'summary', 'month', 'year'));
    }

    public function show(Request $request, $id)
    {
        $attendance = Attendance::with(['user.employeeDetail.department', 'user.employeeDetail.designation', 'shift', 'logs'])
            ->findOrFail($id);

        if (Gate::denies('view', $attendance)) {
            abort(403);
        }

        if ($request->wantsJson()) {
            return response()->json($attendance);
        }

        return view('attendance.show', compact('attendance'));
    }
}
