<?php

namespace App\Services;

use App\Models\User;
use App\Models\SalaryStructure;
use App\Models\SalaryComponent;
use App\Models\Attendance;
use App\Models\LeaveRequestDay;
use App\Models\EmployeeLoan;
use App\Models\SalaryAdvance;
use Carbon\Carbon;

class PayrollCalculationEngine
{
    /**
     * Calculate monthly payroll details for an employee.
     */
    public function calculate(User $employee, int $month, int $year, float $monthlyGross, SalaryStructure $structure): array
    {
        // 1. Determine days in the target month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // 2. Count Loss of Pay (LOP) Days
        // A. Unpaid Leaves: approved leave request days where is_paid = false
        $unpaidLeaveDays = LeaveRequestDay::whereHas('leaveRequest', function ($query) use ($employee) {
                $query->where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereHas('leaveType', function ($q) {
                        $q->where('is_paid', false);
                    });
            })
            ->whereBetween('leave_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('day_weight');

        // B. Absent Days: attendance records with status 'Absent'
        $absentDays = Attendance::where('user_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('attendance_status', 'Absent')
            ->count();

        $lopDays = (float) ($unpaidLeaveDays + $absentDays);
        $paidDays = max(0, $daysInMonth - $lopDays);
        $prorationRatio = $daysInMonth > 0 ? ($paidDays / $daysInMonth) : 0;

        // 3. Resolve components from structure
        $components = $structure->components;

        // Separate earnings and deductions
        $earnings = $components->where('component_type', 'earning')->sortBy('pivot.sort_order');
        $deductions = $components->where('component_type', 'deduction')->sortBy('pivot.sort_order');

        $calculatedItems = [];
        $basicSalaryAmount = 0.00;

        // 4. Calculate Earnings
        // Identify and calculate Basic first, as other components might depend on it
        $basicComponent = $earnings->first(fn($c) => $c->component_code === 'BASIC');
        if ($basicComponent) {
            $basicVal = (float) $basicComponent->pivot->calculation_value;
            $fullBasic = $monthlyGross * ($basicVal / 100);
            $proratedBasic = $fullBasic * $prorationRatio;
            $basicSalaryAmount = $proratedBasic;

            $calculatedItems['BASIC'] = [
                'component_id' => $basicComponent->id,
                'name' => $basicComponent->component_name,
                'code' => 'BASIC',
                'type' => 'earning',
                'amount' => round($proratedBasic, 2),
                'full_amount' => round($fullBasic, 2),
            ];
        }

        foreach ($earnings as $component) {
            if ($component->component_code === 'BASIC') {
                continue;
            }

            $code = $component->component_code;
            $calcType = $component->calculation_type;
            $calcVal = (float) $component->pivot->calculation_value;

            $fullAmount = 0.00;

            if ($calcType === 'fixed') {
                $fullAmount = $calcVal;
            } elseif ($calcType === 'percentage_of_gross') {
                $fullAmount = $monthlyGross * ($calcVal / 100);
            } elseif ($calcType === 'percentage_of_basic') {
                // If Basic doesn't exist, calculate based on gross fraction or component defaults
                $fullBasic = $basicSalaryAmount / ($prorationRatio > 0 ? $prorationRatio : 1);
                $fullAmount = $fullBasic * ($calcVal / 100);
            }

            // Prorate recurring earnings, skip one-offs (e.g. bonus, incentive, arrears)
            $isRecurring = !in_array($code, ['BONUS', 'INCENTIVE', 'ARREARS']);
            $proratedAmount = $isRecurring ? ($fullAmount * $prorationRatio) : $fullAmount;

            $calculatedItems[$code] = [
                'component_id' => $component->id,
                'name' => $component->component_name,
                'code' => $code,
                'type' => 'earning',
                'amount' => round($proratedAmount, 2),
                'full_amount' => round($fullAmount, 2),
            ];
        }

        // Calculate total earnings
        $totalEarnings = array_sum(array_column($calculatedItems, 'amount'));
        $lopDeductionAmount = $monthlyGross * (1 - $prorationRatio);

        // 5. Calculate Overtime (if any)
        $overtimeMinutes = Attendance::where('user_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('overtime_minutes');

        $overtimeHours = $overtimeMinutes / 60;
        $overtimeAmount = 0.00;

        if ($overtimeHours > 0) {
            // Overtime hourly rate = (Monthly Gross / (22 working days * 8 hours)) * 1.5 multiplier
            $hourlyRate = ($monthlyGross / (22 * 8));
            $overtimeAmount = $overtimeHours * $hourlyRate * 1.5;
            
            $calculatedItems['OVERTIME'] = [
                'component_id' => SalaryComponent::where('component_code', 'OVERTIME')->first()?->id ?? 999, // fallback
                'name' => 'Overtime Allowance',
                'code' => 'OVERTIME',
                'type' => 'earning',
                'amount' => round($overtimeAmount, 2),
                'full_amount' => round($overtimeAmount, 2),
            ];
            $totalEarnings += $overtimeAmount;
        }

        // 6. Calculate Deductions
        $totalDeductions = 0.00;

        foreach ($deductions as $component) {
            $code = $component->component_code;
            $calcType = $component->calculation_type;
            $calcVal = (float) $component->pivot->calculation_value;

            $amount = 0.00;

            if ($code === 'PF') {
                // PF is typically 12% of Basic Salary
                $amount = $basicSalaryAmount * ($calcVal / 100);
            } elseif ($code === 'ESI') {
                // ESI is typically 0.75% of Gross Earned Salary
                $amount = $totalEarnings * ($calcVal / 100);
            } elseif ($code === 'PT') {
                // Professional Tax based on standard slab
                if ($totalEarnings >= 15000) {
                    $amount = 200.00;
                } elseif ($totalEarnings >= 10000) {
                    $amount = 150.00;
                } else {
                    $amount = 0.00;
                }
            } elseif ($code === 'TDS' || $code === 'INCOME_TAX') {
                // Income Tax slab calculation
                $amount = $this->calculateTds($totalEarnings * 12);
            } else {
                if ($calcType === 'fixed') {
                    $amount = $calcVal;
                } elseif ($calcType === 'percentage_of_basic') {
                    $amount = $basicSalaryAmount * ($calcVal / 100);
                } elseif ($calcType === 'percentage_of_gross') {
                    $amount = $totalEarnings * ($calcVal / 100);
                }
            }

            $calculatedItems[$code] = [
                'component_id' => $component->id,
                'name' => $component->component_name,
                'code' => $code,
                'type' => 'deduction',
                'amount' => round($amount, 2),
                'full_amount' => round($amount, 2),
            ];
            $totalDeductions += $amount;
        }

        // 7. Loan EMI Recoveries
        $activeLoans = EmployeeLoan::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->get();

        $loanRecoveryAmount = 0.00;
        foreach ($activeLoans as $loan) {
            // Deduct recovery amount up to remaining principal
            $emi = min($loan->monthly_emi, $loan->remaining_principal);
            if ($emi > 0) {
                $loanRecoveryAmount += $emi;
                $calculatedItems['LOAN_' . $loan->id] = [
                    'component_id' => SalaryComponent::where('component_code', 'LOAN_RECOVERY')->first()?->id ?? 888,
                    'name' => 'Loan Recovery (Ref: ' . substr($loan->uuid, 0, 8) . ')',
                    'code' => 'LOAN_RECOVERY',
                    'type' => 'deduction',
                    'amount' => round($emi, 2),
                    'full_amount' => round($emi, 2),
                    'meta' => ['loan_id' => $loan->id]
                ];
            }
        }
        $totalDeductions += $loanRecoveryAmount;

        // 8. Salary Advance Recovery
        $advances = SalaryAdvance::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('recovery_month', $month)
            ->where('recovery_year', $year)
            ->get();

        $advanceRecoveryAmount = 0.00;
        foreach ($advances as $adv) {
            $advanceRecoveryAmount += $adv->amount;
            $calculatedItems['ADVANCE_' . $adv->id] = [
                'component_id' => SalaryComponent::where('component_code', 'ADVANCE_RECOVERY')->first()?->id ?? 777,
                'name' => 'Advance Recovery',
                'code' => 'ADVANCE_RECOVERY',
                'type' => 'deduction',
                'amount' => round($adv->amount, 2),
                'full_amount' => round($adv->amount, 2),
                'meta' => ['advance_id' => $adv->id]
            ];
        }
        $totalDeductions += $advanceRecoveryAmount;

        // 9. Compute Net Salary
        $netSalary = max(0.00, $totalEarnings - $totalDeductions);

        return [
            'total_working_days' => $daysInMonth,
            'paid_days' => $paidDays,
            'lop_days' => $lopDays,
            'monthly_gross_salary' => $monthlyGross,
            'gross_salary_earned' => round($totalEarnings, 2), // gross before non-tax deductions
            'total_earnings' => round($totalEarnings, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_salary' => round($netSalary, 2),
            'items' => $calculatedItems,
        ];
    }

    /**
     * Helper to compute monthly TDS based on annual estimated taxable salary.
     */
    protected function calculateTds(float $annualGross): float
    {
        // Progressive tax slab calculations (Simplified standard progressive tax bands)
        // Standard Deduction: 75,000
        $taxableIncome = max(0.00, $annualGross - 75000);

        $slabs = [
            ['limit' => 300000, 'rate' => 0.00],
            ['limit' => 300000, 'rate' => 0.05], // 3L to 6L
            ['limit' => 300000, 'rate' => 0.10], // 6L to 9L
            ['limit' => 300000, 'rate' => 0.15], // 9L to 12L
            ['limit' => 300000, 'rate' => 0.20], // 12L to 15L
            ['limit' => null,   'rate' => 0.30], // Above 15L
        ];

        $remainingIncome = $taxableIncome;
        $annualTax = 0.00;

        foreach ($slabs as $slab) {
            $limit = $slab['limit'];
            $rate = $slab['rate'];

            if ($limit === null) {
                $annualTax += $remainingIncome * $rate;
                break;
            }

            $taxableInSlab = min($remainingIncome, $limit);
            $annualTax += $taxableInSlab * $rate;
            $remainingIncome -= $taxableInSlab;

            if ($remainingIncome <= 0) {
                break;
            }
        }

        return $annualTax / 12;
    }
}
