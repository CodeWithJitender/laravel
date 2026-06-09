<?php

namespace App\Services;

use App\Models\PayrollRun;
use App\Models\PayrollRunEmployee;
use App\Models\PayrollApproval;
use App\Models\EmployeeLoan;
use App\Models\LoanRepayment;
use App\Models\SalaryAdvance;
use App\Jobs\ProcessPayrollRunJob;
use App\Jobs\GeneratePayslipsJob;
use App\Events\PayrollStarted;
use App\Events\PayrollApproved;
use App\Events\PayrollPublished;
use App\Repositories\PayrollRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    protected $repository;

    public function __construct(PayrollRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Start a new payroll run.
     */
    public function startPayrollRun(int $month, int $year, int $processorId, string $runType = 'monthly'): PayrollRun
    {
        $existingRun = $this->repository->findRunByMonthAndYear($month, $year);

        if ($existingRun) {
            if (in_array($existingRun->status, ['approved', 'published'])) {
                throw new Exception("Payroll run for {$month}/{$year} is already approved or published and cannot be re-run.");
            }
            // If it exists in draft/calculated, delete the existing employees and run to start fresh
            DB::transaction(function () use ($existingRun) {
                $existingRun->employees()->delete();
                $existingRun->approvals()->delete();
                $existingRun->delete();
            });
        }

        $payrollRun = PayrollRun::create([
            'run_month' => $month,
            'run_year' => $year,
            'run_type' => $runType,
            'status' => 'draft',
            'processed_by' => $processorId,
            'total_employees' => 0,
            'total_gross' => 0.00,
            'total_earnings' => 0.00,
            'total_deductions' => 0.00,
            'total_net' => 0.00,
        ]);

        event(new PayrollStarted($payrollRun));

        // Dispatch background processing job
        ProcessPayrollRunJob::dispatch($payrollRun->id);

        return $payrollRun;
    }

    /**
     * Approve the payroll run at a specific level (Finance / HR).
     */
    public function approveRun(int $runId, int $approverId, string $level, string $remarks = null): PayrollApproval
    {
        $payrollRun = PayrollRun::findOrFail($runId);

        if ($payrollRun->status === 'published') {
            throw new Exception("Cannot approve a published payroll run.");
        }

        // Enforce sequence: Finance must approve before HR
        if ($level === 'HR') {
            $hasFinanceApproval = PayrollApproval::where('payroll_run_id', $runId)
                ->where('approval_level', 'Finance')
                ->where('status', 'approved')
                ->exists();

            if (!$hasFinanceApproval) {
                throw new Exception("Finance approval is required before HR approval.");
            }
        }

        return DB::transaction(function () use ($payrollRun, $approverId, $level, $remarks) {
            // Record approval
            $approval = PayrollApproval::create([
                'payroll_run_id' => $payrollRun->id,
                'approver_id' => $approverId,
                'approval_level' => $level,
                'status' => 'approved',
                'remarks' => $remarks,
            ]);

            // Transition status
            if ($level === 'HR') {
                $payrollRun->update([
                    'status' => 'approved',
                    'approved_by' => $approverId,
                    'approved_at' => now(),
                ]);
                event(new PayrollApproved($payrollRun));
            }

            return $approval;
        });
    }

    /**
     * Publish the payroll run.
     */
    public function publishRun(int $runId, int $publisherId): PayrollRun
    {
        $payrollRun = PayrollRun::with('employees.items')->findOrFail($runId);

        if ($payrollRun->status !== 'approved') {
            throw new Exception("Payroll run must be approved by both Finance and HR before it can be published.");
        }

        return DB::transaction(function () use ($payrollRun, $publisherId) {
            // Update run status
            $payrollRun->update([
                'status' => 'published',
            ]);

            // Realize repayments and recoveries
            foreach ($payrollRun->employees as $runEmployee) {
                // Update employee-level status
                $runEmployee->update(['status' => 'published']);

                // 1. Process Loan Repayments
                $loanRecoveryItems = $runEmployee->items->where('component_code', 'LOAN_RECOVERY');
                foreach ($loanRecoveryItems as $item) {
                    // Match active loan for this employee
                    // The calculated items holds loan meta in components or we check employee's active loans
                    $loans = EmployeeLoan::where('employee_id', $runEmployee->employee_id)
                        ->where('status', 'active')
                        ->get();

                    foreach ($loans as $loan) {
                        // Safe check: deduct up to amount
                        $emi = min($item->amount, $loan->remaining_principal);
                        if ($emi > 0) {
                            $loan->decrement('remaining_principal', $emi);
                            
                            LoanRepayment::create([
                                'employee_loan_id' => $loan->id,
                                'payroll_run_employee_id' => $runEmployee->id,
                                'amount' => $emi,
                                'repayment_date' => now()->toDateString(),
                                'payment_method' => 'payroll',
                            ]);

                            if ($loan->remaining_principal <= 0) {
                                $loan->update(['status' => 'repaid']);
                            }
                            break; // Recovered against the active loan
                        }
                    }
                }

                // 2. Process Salary Advance Recoveries
                $advanceRecoveryItems = $runEmployee->items->where('component_code', 'ADVANCE_RECOVERY');
                if ($advanceRecoveryItems->isNotEmpty()) {
                    SalaryAdvance::where('employee_id', $runEmployee->employee_id)
                        ->where('status', 'approved')
                        ->where('recovery_month', $payrollRun->run_month)
                        ->where('recovery_year', $payrollRun->run_year)
                        ->update(['status' => 'recovered']);
                }
            }

            // Dispatch payslip generation job
            GeneratePayslipsJob::dispatch($payrollRun->id);

            // Fire event
            event(new PayrollPublished($payrollRun));

            return $payrollRun;
        });
    }
}
