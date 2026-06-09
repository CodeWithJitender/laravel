<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\LeaveApproval;
use App\Models\LeaveStatusHistory;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;

class LeaveRequestService extends BaseService
{
    protected $balanceService;
    protected $policyService;

    public function __construct(LeaveBalanceService $balanceService, LeavePolicyService $policyService)
    {
        $this->balanceService = $balanceService;
        $this->policyService = $policyService;
    }

    /**
     * Submit a new leave request.
     */
    public function submitRequest(User $employee, array $data)
    {
        return $this->transaction(function () use ($employee, $data) {
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            $leaveTypeId = $data['leave_type_id'];

            // 1. Fetch policy
            $leaveType = LeaveType::with('policy.rules')->findOrFail($leaveTypeId);
            $policy = $leaveType->policy;

            if (!$policy || $policy->status !== 'active') {
                throw new Exception("Leave policy for this leave type is currently inactive or not configured.");
            }

            // 2. Check demographic eligibility
            if (!$this->policyService->isEligible($employee, $policy)) {
                throw new Exception("You are not eligible to apply for this leave type based on organization rules.");
            }

            // 3. Notice Period Constraint
            $noticeDays = (int) $policy->notice_period_days;
            if ($noticeDays > 0) {
                $daysDiff = Carbon::today()->diffInDays($startDate, false);
                if ($daysDiff < $noticeDays) {
                    throw new Exception("This leave type requires a notice period of at least {$noticeDays} days.");
                }
            }

            // 4. Overlap Date Check
            $overlapExists = LeaveRequest::where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function ($sub) use ($startDate, $endDate) {
                              $sub->where('start_date', '<=', $startDate)
                                  ->where('end_date', '>=', $endDate);
                          });
                })->exists();

            if ($overlapExists) {
                throw new Exception("You have already requested or been approved for leave during this period.");
            }

            // 5. Calculate total days weight
            $totalDays = 0.00;
            $daysBreakdown = [];
            $currentDate = $startDate->copy();

            $isHalfDay = !empty($data['half_day']);
            $halfDaySession = $data['half_day_session'] ?? null;

            if ($isHalfDay && !$startDate->eq($endDate)) {
                throw new Exception("Half-day leave requests can only span a single day.");
            }

            while ($currentDate->lessThanOrEqualTo($endDate)) {
                // Skip weekly offs if shift is defined
                $detail = $employee->employeeDetail;
                $shift = $detail?->shift;
                $isOff = false;

                $officeTiming = \App\Models\OfficeTiming::first();
                $weeklyOff = $officeTiming ? $officeTiming->weekly_off : ['Saturday', 'Sunday'];
                $isOff = false;

                if (is_array($weeklyOff)) {
                    $dayName = $currentDate->format('l');
                    if (in_array($dayName, $weeklyOff)) {
                        $isOff = true;
                    }
                }

                // Skip holidays based on location
                if (!$isOff) {
                    $holidayService = app(\App\Services\HolidayService::class);
                    if ($holidayService->isHolidayForUserLocation($employee, $currentDate)) {
                        $isOff = true;
                    }
                }

                if (!$isOff) {
                    $weight = $isHalfDay ? 0.5 : 1.0;
                    $session = $isHalfDay ? $halfDaySession : 'full';
                    
                    $daysBreakdown[] = [
                        'leave_date' => $currentDate->toDateString(),
                        'day_weight' => $weight,
                        'session' => $session,
                    ];
                    $totalDays += $weight;
                }

                $currentDate->addDay();
            }

            if ($totalDays <= 0) {
                throw new Exception("The selected dates contain no active working days.");
            }

            // 6. Max Consecutive Days Constraint
            $maxConsecutive = $policy->max_consecutive_days;
            if ($maxConsecutive > 0 && $totalDays > $maxConsecutive) {
                throw new Exception("This leave type policy permits a maximum of {$maxConsecutive} consecutive days.");
            }

            // 7. Check Balance Availability
            $balance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveTypeId)
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                throw new Exception("Leave balance record not found for this leave type.");
            }

            if ($balance->remaining_balance < $totalDays) {
                throw new Exception("Insufficient leave balance. You have " . number_format($balance->remaining_balance, 2) . " days remaining, but requested " . number_format($totalDays, 2) . " days.");
            }

            // 8. Handle file attachment upload
            $attachmentPath = null;
            if (isset($data['attachment']) && $data['attachment']->isValid()) {
                $attachmentPath = $data['attachment']->store('leave_attachments', 'public');
            }

            // 9. Create Request record
            $request = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_days' => $totalDays,
                'half_day' => $isHalfDay,
                'half_day_session' => $halfDaySession,
                'reason' => $data['reason'],
                'attachment_path' => $attachmentPath,
                'emergency_phone' => $data['emergency_phone'],
                'status' => 'pending',
                'applied_at' => Carbon::now(),
            ]);

            // 10. Store Days breakdown
            foreach ($daysBreakdown as $day) {
                $day['leave_request_id'] = $request->id;
                LeaveRequestDay::create($day);
            }

            // 11. Record history
            LeaveStatusHistory::create([
                'leave_request_id' => $request->id,
                'user_id' => $employee->id,
                'status' => 'pending',
                'remarks' => 'Leave requested by employee.',
            ]);

            // 12. Update pending balance
            $this->balanceService->adjustPendingBalance($employee->id, $leaveTypeId, $totalDays);

            event(new \App\Events\LeaveApplied($request));

            return $request;
        });
    }

    /**
     * Process manager approval.
     */
    public function approveRequest(int $requestId, int $approverId, string $remarks = null)
    {
        return $this->transaction(function () use ($requestId, $approverId, $remarks) {
            $request = LeaveRequest::with(['employee', 'days'])->findOrFail($requestId);

            if ($request->status !== 'pending') {
                throw new Exception("This leave request has already been processed.");
            }

            // Check if approver is the employee's manager
            $detail = $request->employee->employeeDetail;
            $approver = User::find($approverId);

            if (!$approver->hasRole('Admin') && (!$detail || $detail->manager_id !== $approverId)) {
                throw new Exception("You are not authorized to approve this leave request.");
            }

            // Record approval signature
            LeaveApproval::create([
                'leave_request_id' => $request->id,
                'approver_id' => $approverId,
                'level' => 1,
                'status' => 'approved',
                'remarks' => $remarks,
            ]);

            // Update status
            $request->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => Carbon::now(),
            ]);

            // Record status history
            LeaveStatusHistory::create([
                'leave_request_id' => $request->id,
                'user_id' => $approverId,
                'status' => 'approved',
                'remarks' => $remarks ?? 'Approved by manager.',
            ]);

            // Finalize balance deduction
            $this->balanceService->finalizeDeduction($request->employee_id, $request->leave_type_id, (float) $request->total_days);

            // Sync with attendance
            $this->syncAttendanceOnApproval($request);

            event(new \App\Events\LeaveApproved($request));

            return $request;
        });
    }

    /**
     * Process manager rejection.
     */
    public function rejectRequest(int $requestId, int $approverId, string $remarks)
    {
        return $this->transaction(function () use ($requestId, $approverId, $remarks) {
            $request = LeaveRequest::with('employee')->findOrFail($requestId);

            if ($request->status !== 'pending') {
                throw new Exception("This leave request has already been processed.");
            }

            // Check authorization
            $detail = $request->employee->employeeDetail;
            $approver = User::find($approverId);

            if (!$approver->hasRole('Admin') && (!$detail || $detail->manager_id !== $approverId)) {
                throw new Exception("You are not authorized to reject this leave request.");
            }

            // Record approval log
            LeaveApproval::create([
                'leave_request_id' => $request->id,
                'approver_id' => $approverId,
                'level' => 1,
                'status' => 'rejected',
                'remarks' => $remarks,
            ]);

            // Update status
            $request->update([
                'status' => 'rejected',
            ]);

            // Record history
            LeaveStatusHistory::create([
                'leave_request_id' => $request->id,
                'user_id' => $approverId,
                'status' => 'rejected',
                'remarks' => $remarks,
            ]);

            // Release pending balance
            $this->balanceService->releasePending($request->employee_id, $request->leave_type_id, (float) $request->total_days);

            event(new \App\Events\LeaveRejected($request));

            return $request;
        });
    }

    /**
     * Sync approved leave request days directly into the attendance table.
     */
    protected function syncAttendanceOnApproval(LeaveRequest $request)
    {
        foreach ($request->days as $day) {
            $status = $request->leaveType->code === 'WFH' ? 'Work From Home' : 'On Leave';

            // Find or create attendance record
            $attendance = Attendance::firstOrCreate(
                [
                    'user_id' => $request->employee_id,
                    'attendance_date' => $day->leave_date->toDateString(),
                ],
                [
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'shift_id' => $request->employee->employeeDetail?->shift_id,
                ]
            );

            // Update status
            $attendance->attendance_status = $status;
            $attendance->remarks = "Approved Leave: " . $request->leaveType->name;

            if ($day->day_weight == 0.5) {
                // If it is half day, set worked hours status as half day
                $attendance->attendance_status = 'Half Day';
                $attendance->remarks = "Approved Half-Day Leave (" . strtoupper($day->session) . "): " . $request->leaveType->name;
            }

            $attendance->save();
        }
    }
}
