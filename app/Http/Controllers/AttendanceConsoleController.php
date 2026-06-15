<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

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

    public function downloadTemplate()
    {
        if (Gate::denies('attendance.create')) {
            abort(403);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_import_template.csv"',
        ];

        $columns = [
            'Employee Email', 'Employee Code', 'Date', 'Clock In', 'Clock Out', 'Status', 'Remarks'
        ];

        // Fetch a real active user to populate a demo row
        $demoUser = User::whereHas('employeeDetail')->first();
        $demoRow = [
            $demoUser ? $demoUser->email : 'employee@company.com',
            $demoUser ? ($demoUser->employeeDetail->employee_code ?? 'EMP-001') : 'EMP-001',
            date('Y-m-d'),
            date('Y-m-d') . ' 09:00:00',
            date('Y-m-d') . ' 18:00:00',
            'Present',
            'Standard shift completed.'
        ];

        return response()->stream(function () use ($columns, $demoRow) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $demoRow);
            fclose($file);
        }, 200, $headers);
    }

    public function import(Request $request)
    {
        if (Gate::denies('attendance.create')) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return redirect()->back()->with('error', 'Unable to open the uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'The uploaded file is empty.');
        }

        // Clean headers
        $headers = array_map(function ($h) {
            return strtolower(str_replace([' ', '_', '-'], '', trim($h)));
        }, $header);

        $successCount = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                continue;
            }

            $data = [];
            foreach ($headers as $index => $key) {
                $data[$key] = isset($row[$index]) ? trim($row[$index]) : '';
            }

            $email = $data['employeeemail'] ?? '';
            $code = $data['employeecode'] ?? '';
            $dateStr = $data['date'] ?? '';
            $clockInStr = $data['clockin'] ?? '';
            $clockOutStr = $data['clockout'] ?? '';
            $status = $data['status'] ?? 'Present';
            $remarks = $data['remarks'] ?? null;

            if (empty($email) && empty($code)) {
                $errors[] = "Row {$rowNumber}: Either Employee Email or Employee Code is required.";
                continue;
            }

            if (empty($dateStr) || !strtotime($dateStr)) {
                $errors[] = "Row {$rowNumber}: Valid Date is required.";
                continue;
            }

            $dateFormatted = date('Y-m-d', strtotime($dateStr));

            // Find user
            $userQuery = User::query();
            if (!empty($email)) {
                $userQuery->where('email', $email);
            } else {
                $userQuery->whereHas('employeeDetail', function($q) use ($code) {
                    $q->where('employee_code', $code);
                });
            }
            $user = $userQuery->first();

            if (!$user) {
                $errors[] = "Row {$rowNumber}: Employee not found matching Email '{$email}' or Code '{$code}'.";
                continue;
            }

            try {
                DB::transaction(function () use (
                    $user, $dateFormatted, $clockInStr, $clockOutStr, $status, $remarks, &$successCount
                ) {
                    $shift = $user->employeeDetail->shift ?? \App\Models\Shift::first();
                    
                    $clockIn = !empty($clockInStr) ? Carbon::parse($clockInStr) : null;
                    $clockOut = !empty($clockOutStr) ? Carbon::parse($clockOutStr) : null;
                    
                    $workedHours = 0.00;
                    $lateMin = 0;
                    $earlyExitMin = 0;
                    $otMin = 0;

                    if ($clockIn && $clockOut) {
                        $workedHours = round($clockIn->diffInMinutes($clockOut) / 60, 2);

                        if ($shift) {
                            $shiftStart = Carbon::parse($dateFormatted . ' ' . $shift->start_time);
                            $shiftEnd = Carbon::parse($dateFormatted . ' ' . $shift->end_time);
                            $grace = $shift->grace_period_minutes ?? 15;

                            // Late calculation
                            if ($clockIn->greaterThan($shiftStart->copy()->addMinutes($grace))) {
                                $lateMin = $clockIn->diffInMinutes($shiftStart);
                            }

                            // Early exit / Overtime
                            if ($clockOut->lessThan($shiftEnd)) {
                                $earlyExitMin = $clockOut->diffInMinutes($shiftEnd);
                            } elseif ($clockOut->greaterThan($shiftEnd)) {
                                $otMin = $clockOut->diffInMinutes($shiftEnd);
                            }
                        }
                    }

                    // Create or update attendance
                    $attendance = Attendance::updateOrCreate(
                        ['user_id' => $user->id, 'attendance_date' => $dateFormatted],
                        [
                            'shift_id' => $shift ? $shift->id : null,
                            'clock_in' => $clockIn ? $clockIn->toDateTimeString() : null,
                            'clock_out' => $clockOut ? $clockOut->toDateTimeString() : null,
                            'worked_hours' => $workedHours,
                            'late_minutes' => $lateMin,
                            'early_exit_minutes' => $earlyExitMin,
                            'overtime_minutes' => $otMin,
                            'attendance_status' => $status,
                            'remarks' => $remarks,
                        ]
                    );

                    // Re-aggregate monthly summary for this month/year
                    $carbonDate = Carbon::parse($dateFormatted);
                    $month = $carbonDate->month;
                    $year = $carbonDate->year;

                    $attendancesQuery = Attendance::where('user_id', $user->id)
                        ->whereMonth('attendance_date', $month)
                        ->whereYear('attendance_date', $year);

                    $counts = $attendancesQuery->select('attendance_status', DB::raw('count(*) as count'))
                        ->groupBy('attendance_status')
                        ->pluck('count', 'attendance_status')
                        ->toArray();

                    $totalWorkedHours = $attendancesQuery->sum('worked_hours');
                    $totalOvertimeMinutes = $attendancesQuery->sum('overtime_minutes');

                    \App\Models\AttendanceMonthlySummary::updateOrCreate(
                        ['user_id' => $user->id, 'month' => $month, 'year' => $year],
                        [
                            'present_days' => $counts['Present'] ?? 0,
                            'absent_days' => $counts['Absent'] ?? 0,
                            'late_days' => $counts['Late'] ?? 0,
                            'leave_days' => $counts['On Leave'] ?? 0,
                            'holiday_days' => $counts['Holiday'] ?? 0,
                            'wfh_days' => $counts['Work From Home'] ?? 0,
                            'missed_punch_days' => $counts['Missed Punch'] ?? 0,
                            'total_working_hours' => $totalWorkedHours,
                            'total_overtime_hours' => round($totalOvertimeMinutes / 60, 2),
                        ]
                    );

                    $successCount++;
                });
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber} (" . $user->name . "): " . $e->getMessage();
            }
        }

        fclose($handle);

        $flashMessage = "Successfully imported {$successCount} attendance record(s).";

        if (!empty($errors)) {
            return redirect()->route('attendance.index')
                ->with('success', $flashMessage)
                ->with('import_errors', $errors);
        }

        return redirect()->route('attendance.index')->with('success', $flashMessage);
    }
}
