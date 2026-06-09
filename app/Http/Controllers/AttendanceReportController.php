<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendanceReportController extends Controller
{
    public function index()
    {
        if (Gate::denies('attendance.report.view')) {
            abort(403);
        }

        $departments = Department::where('status', 'active')->orderBy('department_name')->get();
        $locations = Location::where('status', 'active')->orderBy('location_name')->get();

        return view('attendance.reports', compact('departments', 'locations'));
    }

    public function generate(Request $request)
    {
        if (Gate::denies('attendance.report.view')) {
            abort(403);
        }

        $request->validate([
            'report_type' => 'required|string|in:daily,monthly,missed_punch,late,overtime',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'export' => 'nullable|string|in:csv',
        ]);

        $type = $request->input('report_type');
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $deptId = $request->input('department_id');
        $locId = $request->input('location_id');

        $query = Attendance::with(['user.employeeDetail.department', 'user.employeeDetail.location', 'shift'])
            ->whereBetween('attendance_date', [$start, $end]);

        if ($deptId) {
            $query->whereHas('user.employeeDetail', function ($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
        }

        if ($locId) {
            $query->whereHas('user.employeeDetail', function ($q) use ($locId) {
                $q->where('location_id', $locId);
            });
        }

        // Filter based on report type
        if ($type === 'missed_punch') {
            $query->where('attendance_status', 'Missed Punch');
        } elseif ($type === 'late') {
            $query->where('late_minutes', '>', 0);
        } elseif ($type === 'overtime') {
            $query->where('overtime_minutes', '>', 0);
        }

        $results = $query->orderBy('attendance_date', 'desc')->get();

        if ($request->input('export') === 'csv') {
            return $this->exportToCsv($results, $type);
        }

        $departments = Department::where('status', 'active')->orderBy('department_name')->get();
        $locations = Location::where('status', 'active')->orderBy('location_name')->get();

        return view('attendance.reports', compact('departments', 'locations', 'results', 'type', 'start', 'end', 'deptId', 'locId'));
    }

    private function exportToCsv($results, $type)
    {
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=attendance_report_" . $type . "_" . date('Ymd_His') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($results) {
            $file = fopen('php://output', 'w');
            
            // CSV Columns
            fputcsv($file, ['Date', 'Emp Code', 'Employee Name', 'Department', 'Location', 'Clock In', 'Clock Out', 'Worked Hours', 'Late (Mins)', 'Early Exit (Mins)', 'Overtime (Mins)', 'Status']);

            foreach ($results as $row) {
                fputcsv($file, [
                    $row->attendance_date->toDateString(),
                    $row->user->employeeDetail?->employee_code ?? '-',
                    $row->user->name,
                    $row->user->employeeDetail?->department?->department_name ?? '-',
                    $row->user->employeeDetail?->location?->location_name ?? '-',
                    $row->clock_in ? $row->clock_in->toDateTimeString() : '-',
                    $row->clock_out ? $row->clock_out->toDateTimeString() : '-',
                    $row->worked_hours,
                    $row->late_minutes,
                    $row->early_exit_minutes,
                    $row->overtime_minutes,
                    $row->attendance_status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
