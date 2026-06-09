<?php

namespace App\Jobs;

use App\Models\PayrollRun;
use App\Models\PayrollRunEmployee;
use App\Models\PayrollItem;
use App\Models\User;
use App\Services\PayrollCalculationEngine;
use App\Events\PayrollCalculated;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessPayrollRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payrollRunId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $payrollRunId)
    {
        $this->payrollRunId = $payrollRunId;
    }

    /**
     * Execute the job.
     */
    public function handle(PayrollCalculationEngine $engine)
    {
        $payrollRun = PayrollRun::find($this->payrollRunId);
        if (!$payrollRun || !in_array($payrollRun->status, ['draft', 'processing'])) {
            return;
        }

        $payrollRun->update(['status' => 'processing']);

        try {
            DB::transaction(function () use ($payrollRun, $engine) {
                // Find all active employees who have an active structure
                $employees = User::where('status', 'active')
                    ->whereHas('employeeSalaryStructures', function ($query) {
                        $query->where('status', 'active');
                    })
                    ->get();

                $totalGross = 0.00;
                $totalEarnings = 0.00;
                $totalDeductions = 0.00;
                $totalNet = 0.00;
                $employeeCount = 0;

                // Delete existing run employee records for this run if any (re-runs)
                $payrollRun->employees()->delete();

                foreach ($employees as $employee) {
                    // Find active salary structure for this employee
                    $activeStructureRelation = $employee->employeeSalaryStructures()
                        ->where('status', 'active')
                        ->where('effective_from', '<=', now()->toDateString())
                        ->where(function ($query) {
                            $query->whereNull('effective_to')
                                  ->orWhere('effective_to', '>=', now()->toDateString());
                        })
                        ->first();
                    
                    if (!$activeStructureRelation) {
                        // Fallback: take the latest active structure
                        $activeStructureRelation = $employee->employeeSalaryStructures()
                            ->where('status', 'active')
                            ->orderBy('effective_from', 'desc')
                            ->first();
                    }

                    if (!$activeStructureRelation) {
                        continue;
                    }

                    $structure = $activeStructureRelation->salaryStructure;
                    if (!$structure) {
                        continue;
                    }

                    // Perform calculation
                    $calcResult = $engine->calculate(
                        $employee,
                        $payrollRun->run_month,
                        $payrollRun->run_year,
                        (float) $activeStructureRelation->monthly_gross_salary,
                        $structure
                    );

                    // Save PayrollRunEmployee
                    $runEmployee = PayrollRunEmployee::create([
                        'payroll_run_id' => $payrollRun->id,
                        'employee_id' => $employee->id,
                        'salary_structure_id' => $structure->id,
                        'monthly_gross_salary' => $calcResult['monthly_gross_salary'],
                        'total_working_days' => $calcResult['total_working_days'],
                        'paid_days' => $calcResult['paid_days'],
                        'lop_days' => $calcResult['lop_days'],
                        'gross_salary_earned' => $calcResult['gross_salary_earned'],
                        'total_earnings' => $calcResult['total_earnings'],
                        'total_deductions' => $calcResult['total_deductions'],
                        'net_salary' => $calcResult['net_salary'],
                        'status' => 'calculated',
                    ]);

                    // Save PayrollItems
                    foreach ($calcResult['items'] as $code => $item) {
                        PayrollItem::create([
                            'payroll_run_employee_id' => $runEmployee->id,
                            'salary_component_id' => $item['component_id'],
                            'component_name' => $item['name'],
                            'component_code' => $item['code'],
                            'component_type' => $item['type'],
                            'amount' => $item['amount'],
                        ]);
                    }

                    $totalGross += (float) $activeStructureRelation->monthly_gross_salary;
                    $totalEarnings += (float) $calcResult['total_earnings'];
                    $totalDeductions += (float) $calcResult['total_deductions'];
                    $totalNet += (float) $calcResult['net_salary'];
                    $employeeCount++;
                }

                // Update Payroll Run details
                $payrollRun->update([
                    'status' => 'calculated',
                    'total_employees' => $employeeCount,
                    'total_gross' => $totalGross,
                    'total_earnings' => $totalEarnings,
                    'total_deductions' => $totalDeductions,
                    'total_net' => $totalNet,
                    'processed_at' => now(),
                ]);
            });

            event(new PayrollCalculated($payrollRun));

        } catch (Exception $e) {
            $payrollRun->update(['status' => 'draft']);
            throw $e;
        }
    }
}
