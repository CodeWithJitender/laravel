<?php

namespace App\Services;

use App\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\AttendanceMonthlySummary;
use App\Services\WorkingHoursEngine;
use App\Services\OfficeTimingService;
use Carbon\Carbon;
use Exception;

class ClockingService extends BaseService
{
    protected $attendanceRepo;
    protected $hoursEngine;
    protected $officeTimingService;

    public function __construct(
        AttendanceRepositoryInterface $attendanceRepo,
        WorkingHoursEngine $hoursEngine,
        OfficeTimingService $officeTimingService
    ) {
        $this->attendanceRepo = $attendanceRepo;
        $this->hoursEngine = $hoursEngine;
        $this->officeTimingService = $officeTimingService;
    }

    public function clockIn($user, array $data)
    {
        return $this->transaction(function () use ($user, $data) {
            $today = Carbon::today()->toDateString();

            // Check if user already clocked in today
            $exists = Attendance::where('user_id', $user->id)
                ->where('attendance_date', $today)
                ->exists();

            if ($exists) {
                throw new Exception("You have already clocked in for today.");
            }

            $detail = $user->employeeDetail;
            if (!$detail || !$detail->shift) {
                throw new Exception("No active shift assigned to your employee profile.");
            }

            $now = Carbon::now();
            
            // Initial calculations for clock in (to set status to Present or Late)
            $shift = $detail->shift->toArray();
            $officeRules = $this->officeTimingService->getDefault()->toArray();

            $calc = $this->hoursEngine->calculate($now, null, $shift, $officeRules);

            // Create attendance record
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'attendance_date' => $today,
                'shift_id' => $detail->shift_id,
                'clock_in' => $now,
                'clock_out' => null,
                'worked_hours' => 0.00,
                'late_minutes' => $calc['late_minutes'],
                'early_exit_minutes' => 0,
                'overtime_minutes' => 0,
                'attendance_status' => $calc['late_minutes'] > 0 ? 'Late' : 'Present',
                'remarks' => $data['remarks'] ?? null,
            ]);

            // Create log
            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'type' => 'clock_in',
                'log_time' => $now,
                'ip_address' => $data['ip_address'] ?? null,
                'device_info' => $data['device_info'] ?? null,
                'method' => $data['method'] ?? 'web',
            ]);

            $this->updateMonthlySummary($user->id, $now->month, $now->year);

            return $attendance;
        });
    }

    public function clockOut($user, array $data)
    {
        return $this->transaction(function () use ($user, $data) {
            // Find an open punch (clock_out is null)
            $attendance = Attendance::where('user_id', $user->id)
                ->whereNull('clock_out')
                ->orderBy('attendance_date', 'desc')
                ->first();

            if (!$attendance) {
                throw new Exception("No active clock-in session found. You must clock in first.");
            }

            $now = Carbon::now();

            // Set clock out
            $attendance->clock_out = $now;
            
            $shift = $attendance->shift ? $attendance->shift->toArray() : null;
            if (!$shift) {
                $detail = $user->employeeDetail;
                $shift = $detail && $detail->shift ? $detail->shift->toArray() : null;
            }

            if (!$shift) {
                throw new Exception("Shift context missing for attendance calculation.");
            }

            $officeRules = $this->officeTimingService->getDefault()->toArray();

            // Calculate metrics
            $calc = $this->hoursEngine->calculate(Carbon::parse($attendance->clock_in), $now, $shift, $officeRules);

            $attendance->worked_hours = $calc['worked_hours'];
            $attendance->late_minutes = $calc['late_minutes'];
            $attendance->early_exit_minutes = $calc['early_exit_minutes'];
            $attendance->overtime_minutes = $calc['overtime_minutes'];
            $attendance->attendance_status = $calc['status'];
            $attendance->save();

            // Create log
            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'type' => 'clock_out',
                'log_time' => $now,
                'ip_address' => $data['ip_address'] ?? null,
                'device_info' => $data['device_info'] ?? null,
                'method' => $data['method'] ?? 'web',
            ]);

            $clockInTime = Carbon::parse($attendance->clock_in);
            $this->updateMonthlySummary($user->id, $clockInTime->month, $clockInTime->year);

            return $attendance;
        });
    }

    public function updateMonthlySummary(int $userId, int $month, int $year)
    {
        $attendances = Attendance::where('user_id', $userId)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get();

        $stats = [
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
            'leave_days' => 0,
            'holiday_days' => 0,
            'wfh_days' => 0,
            'missed_punch_days' => 0,
            'total_working_hours' => 0.00,
            'total_overtime_hours' => 0.00,
        ];

        foreach ($attendances as $att) {
            $stats['total_working_hours'] += (float) $att->worked_hours;
            $stats['total_overtime_hours'] += ((float) $att->overtime_minutes) / 60;

            switch ($att->attendance_status) {
                case 'Present':
                    $stats['present_days']++;
                    break;
                case 'Absent':
                    $stats['absent_days']++;
                    break;
                case 'Late':
                    $stats['late_days']++;
                    $stats['present_days']++; // Late still counts as present day
                    break;
                case 'On Leave':
                    $stats['leave_days']++;
                    break;
                case 'Holiday':
                    $stats['holiday_days']++;
                    break;
                case 'Work From Home':
                    $stats['wfh_days']++;
                    break;
                case 'Missed Punch':
                    $stats['missed_punch_days']++;
                    break;
            }
        }

        AttendanceMonthlySummary::updateOrCreate(
            ['user_id' => $userId, 'month' => $month, 'year' => $year],
            $stats
        );
    }
}
