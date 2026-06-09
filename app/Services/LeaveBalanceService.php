<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\LeavePolicy;
use App\Models\LeaveAccrual;
use App\Models\LeaveCarryForward;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService extends BaseService
{
    /**
     * Initialize empty or default leave balances for a new employee.
     */
    public function initializeEmployeeBalances(User $user)
    {
        $this->transaction(function () use ($user) {
            $leaveTypes = LeaveType::where('status', 'active')->get();

            foreach ($leaveTypes as $type) {
                $policy = $type->policy;
                $allocated = 0.00;
                
                if ($policy && $policy->status === 'active' && !$policy->monthly_accrual) {
                    $allocated = (float) $policy->annual_allocation;
                }

                LeaveBalance::firstOrCreate(
                    ['employee_id' => $user->id, 'leave_type_id' => $type->id],
                    [
                        'opening_balance' => $allocated,
                        'allocated_balance' => $allocated,
                        'accrued_balance' => 0.00,
                        'used_balance' => 0.00,
                        'pending_balance' => 0.00,
                        'carry_forward_balance' => 0.00,
                    ]
                );
            }
        });
    }

    /**
     * Run monthly accruals.
     */
    public function runMonthlyAccruals(Carbon $runDate)
    {
        return $this->transaction(function () use ($runDate) {
            $policies = LeavePolicy::where('status', 'active')
                ->where('monthly_accrual', true)
                ->get();

            $accruedCount = 0;

            foreach ($policies as $policy) {
                $leaveType = $policy->leaveType;
                $accrualAmount = (float) ($policy->annual_allocation / 12);

                // Fetch active users
                $users = User::where('status', 'active')->get();

                foreach ($users as $user) {
                    // Check eligibility via LeavePolicyService
                    $policyService = app(LeavePolicyService::class);
                    if (!$policyService->isEligible($user, $policy)) {
                        continue;
                    }

                    // Pessimistic lock balance row
                    $balance = LeaveBalance::firstOrCreate(
                        ['employee_id' => $user->id, 'leave_type_id' => $leaveType->id],
                        [
                            'opening_balance' => 0.00,
                            'allocated_balance' => 0.00,
                            'accrued_balance' => 0.00,
                            'used_balance' => 0.00,
                            'pending_balance' => 0.00,
                            'carry_forward_balance' => 0.00,
                        ]
                    );

                    // Re-fetch with lock
                    $lockedBalance = LeaveBalance::where('id', $balance->id)->lockForUpdate()->first();
                    $lockedBalance->increment('accrued_balance', $accrualAmount);

                    // Log accrual transaction
                    LeaveAccrual::create([
                        'employee_id' => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'accrued_amount' => $accrualAmount,
                        'run_date' => $runDate->toDateString(),
                    ]);

                    $accruedCount++;
                }
            }

            return $accruedCount;
        });
    }

    /**
     * Run year-end carry forward adjustments.
     */
    public function runYearEndCarryForwards(int $year)
    {
        return $this->transaction(function () use ($year) {
            $balances = LeaveBalance::with('leaveType.policy')->get();
            $processedCount = 0;

            foreach ($balances as $balance) {
                $policy = $balance->leaveType?->policy;
                if (!$policy || $policy->status !== 'active') {
                    continue;
                }

                $remaining = $balance->remaining_balance;
                $limit = (float) $policy->carry_forward_limit;

                $carried = min($remaining, $limit);
                $expired = max(0.00, $remaining - $carried);

                // Lock row
                $lockedBalance = LeaveBalance::where('id', $balance->id)->lockForUpdate()->first();

                // Reset balances for new year
                $lockedBalance->update([
                    'opening_balance' => $carried,
                    'allocated_balance' => !$policy->monthly_accrual ? (float) $policy->annual_allocation : 0.00,
                    'accrued_balance' => 0.00,
                    'used_balance' => 0.00,
                    'pending_balance' => 0.00,
                    'carry_forward_balance' => $carried,
                ]);

                // Log CF details
                LeaveCarryForward::create([
                    'employee_id' => $balance->employee_id,
                    'leave_type_id' => $balance->leave_type_id,
                    'amount_carried' => $carried,
                    'amount_expired' => $expired,
                    'run_year' => $year,
                ]);

                $processedCount++;
            }

            return $processedCount;
        });
    }

    /**
     * Modify pending balances in response to leave request updates.
     */
    public function adjustPendingBalance(int $employeeId, int $leaveTypeId, float $amount)
    {
        $this->transaction(function () use ($employeeId, $leaveTypeId, $amount) {
            $balance = LeaveBalance::firstOrCreate(
                ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId]
            );

            $locked = LeaveBalance::where('id', $balance->id)->lockForUpdate()->first();
            $locked->increment('pending_balance', $amount);
        });
    }

    /**
     * Process deductions when request is approved.
     */
    public function finalizeDeduction(int $employeeId, int $leaveTypeId, float $amount)
    {
        $this->transaction(function () use ($employeeId, $leaveTypeId, $amount) {
            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->lockForUpdate()
                ->firstOrFail();

            // Release pending and move to used
            $balance->decrement('pending_balance', $amount);
            $balance->increment('used_balance', $amount);
        });
    }

    /**
     * Release pending when rejected/cancelled.
     */
    public function releasePending(int $employeeId, int $leaveTypeId, float $amount)
    {
        $this->transaction(function () use ($employeeId, $leaveTypeId, $amount) {
            $balance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->lockForUpdate()
                ->firstOrFail();

            $balance->decrement('pending_balance', $amount);
        });
    }
}
