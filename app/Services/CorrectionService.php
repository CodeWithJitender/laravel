<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceLog;
use App\Services\WorkingHoursEngine;
use App\Services\ClockingService;
use App\Services\OfficeTimingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Exception;

class CorrectionService extends BaseService
{
    protected $hoursEngine;
    protected $clockingService;
    protected $officeTimingService;

    public function __construct(
        WorkingHoursEngine $hoursEngine,
        ClockingService $clockingService,
        OfficeTimingService $officeTimingService
    ) {
        $this->hoursEngine = $hoursEngine;
        $this->clockingService = $clockingService;
        $this->officeTimingService = $officeTimingService;
    }

    public function submitCorrection($user, array $data)
    {
        return $this->transaction(function () use ($user, $data) {
            $requestedDate = $data['requested_date'];

            // Find or create attendance record for that date
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $user->id, 'attendance_date' => $requestedDate],
                [
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'shift_id' => $user->employeeDetail?->shift_id,
                    'attendance_status' => 'Absent'
                ]
            );

            // Check if there is already a pending correction for this date
            $pendingExists = AttendanceCorrection::where('user_id', $user->id)
                ->where('requested_date', $requestedDate)
                ->where('status', 'pending')
                ->exists();

            if ($pendingExists) {
                throw new Exception("You already have a pending correction request for this date.");
            }

            // Handle file upload
            $attachmentPath = null;
            if (isset($data['attachment']) && $data['attachment']->isValid()) {
                $attachmentPath = $data['attachment']->store('attendance_attachments', 'public');
            }

            $requestedClockIn = Carbon::parse($requestedDate . ' ' . $data['requested_clock_in']);
            $requestedClockOut = Carbon::parse($requestedDate . ' ' . $data['requested_clock_out']);
            
            // Adjust clock out date if it crosses midnight
            if ($requestedClockOut->lessThan($requestedClockIn)) {
                $requestedClockOut->addDay();
            }

            $correction = AttendanceCorrection::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'requested_date' => $requestedDate,
                'requested_clock_in' => $requestedClockIn,
                'requested_clock_out' => $requestedClockOut,
                'reason' => $data['reason'],
                'attachment_path' => $attachmentPath,
                'status' => 'pending',
            ]);

            event(new \App\Events\AttendanceCorrectionSubmitted($correction));

            return $correction;
        });
    }

    public function approveCorrection(int $correctionId, int $managerId)
    {
        return $this->transaction(function () use ($correctionId, $managerId) {
            $correction = AttendanceCorrection::findOrFail($correctionId);

            if ($correction->status !== 'pending') {
                throw new Exception("This correction request has already been processed.");
            }

            $correction->update([
                'status' => 'approved',
                'approved_by' => $managerId,
                'approved_at' => Carbon::now(),
            ]);

            // Update attendance record
            $attendance = $correction->attendance;
            $attendance->clock_in = $correction->requested_clock_in;
            $attendance->clock_out = $correction->requested_clock_out;

            $shift = $attendance->shift ? $attendance->shift->toArray() : null;
            if (!$shift && $attendance->user->employeeDetail) {
                $shift = $attendance->user->employeeDetail->shift ? $attendance->user->employeeDetail->shift->toArray() : null;
            }

            if (!$shift) {
                throw new Exception("Employee shift parameters not found.");
            }

            $officeRules = $this->officeTimingService->getDefault()->toArray();

            // Run calculations
            $calc = $this->hoursEngine->calculate(
                Carbon::parse($correction->requested_clock_in),
                Carbon::parse($correction->requested_clock_out),
                $shift,
                $officeRules
            );

            $attendance->worked_hours = $calc['worked_hours'];
            $attendance->late_minutes = $calc['late_minutes'];
            $attendance->early_exit_minutes = $calc['early_exit_minutes'];
            $attendance->overtime_minutes = $calc['overtime_minutes'];
            $attendance->attendance_status = $calc['status'];
            $attendance->remarks = ($attendance->remarks ? $attendance->remarks . "\n" : '') . "Correction Approved by Manager.";
            $attendance->save();

            // Create log entries for audit trail
            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'type' => 'clock_in',
                'log_time' => $correction->requested_clock_in,
                'method' => 'web',
                'device_info' => 'Correction Approved by Manager',
            ]);

            AttendanceLog::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'type' => 'clock_out',
                'log_time' => $correction->requested_clock_out,
                'method' => 'web',
                'device_info' => 'Correction Approved by Manager',
            ]);

            // Update monthly summary
            $inDate = Carbon::parse($correction->requested_clock_in);
            $this->clockingService->updateMonthlySummary($attendance->user_id, $inDate->month, $inDate->year);

            event(new \App\Events\AttendanceCorrectionApproved($correction));

            return $correction;
        });
    }

    public function rejectCorrection(int $correctionId, int $managerId, string $reason)
    {
        return $this->transaction(function () use ($correctionId, $managerId, $reason) {
            $correction = AttendanceCorrection::findOrFail($correctionId);

            if ($correction->status !== 'pending') {
                throw new Exception("This correction request has already been processed.");
            }

            $correction->update([
                'status' => 'rejected',
                'approved_by' => $managerId,
                'approved_at' => Carbon::now(),
                'rejection_reason' => $reason,
            ]);

            return $correction;
        });
    }
}
