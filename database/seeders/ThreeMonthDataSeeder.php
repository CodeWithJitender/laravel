<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Holiday;
use App\Models\HolidayType;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Models\LeaveBalance;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\AttendanceMonthlySummary;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\EmployeeSalaryStructure;
use App\Models\PayrollRun;
use App\Models\PayrollRunEmployee;
use App\Models\PayrollItem;
use App\Models\Payslip;
use Carbon\Carbon;

class ThreeMonthDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Starting ThreeMonthDataSeeder...');

            // 1. Fetch Active Employees
            $employees = User::whereNull('deleted_at')
                ->where('id', '!=', 1)
                ->get();
            
            if ($employees->isEmpty()) {
                $this->command->warn('No active employees found to seed data for.');
                return;
            }

            $this->command->info('Found ' . $employees->count() . ' active employees.');

            // 2. Initialize Leave Balances and Salary Structures
            $standardStructure = SalaryStructure::where('status', 'active')->first();
            if (!$standardStructure) {
                $standardStructure = SalaryStructure::create([
                    'name' => 'Standard Salary Structure',
                    'description' => 'Default company structure with standard earnings and deductions.',
                    'status' => 'active',
                ]);
            }

            // Ensure standard components are mapped to structure if missing
            $components = SalaryComponent::where('status', 'active')->get();
            if ($standardStructure->components()->count() === 0 && $components->isNotEmpty()) {
                $syncArray = [];
                $order = 1;
                foreach ($components as $comp) {
                    $syncArray[$comp->id] = ['calculation_value' => $comp->default_value, 'sort_order' => $order++];
                }
                $standardStructure->components()->sync($syncArray);
            }

            $leaveTypes = LeaveType::where('status', 'active')->get();
            $balanceService = app(\App\Services\LeaveBalanceService::class);

            foreach ($employees as $emp) {
                // Initialize leave balances if missing
                foreach ($leaveTypes as $lt) {
                    LeaveBalance::firstOrCreate(
                        ['employee_id' => $emp->id, 'leave_type_id' => $lt->id],
                        [
                            'opening_balance' => 12.00,
                            'allocated_balance' => 12.00,
                            'accrued_balance' => 0.00,
                            'used_balance' => 0.00,
                            'pending_balance' => 0.00,
                            'carry_forward_balance' => 0.00,
                        ]
                    );
                }

                // Initialize employee salary structure if missing
                $hasSalary = EmployeeSalaryStructure::where('employee_id', $emp->id)->where('status', 'active')->exists();
                if (!$hasSalary) {
                    $isManager = $emp->hasRole('Manager');
                    $gross = $isManager ? rand(80000, 115000) : rand(35000, 70000);
                    EmployeeSalaryStructure::create([
                        'employee_id' => $emp->id,
                        'salary_structure_id' => $standardStructure->id,
                        'effective_from' => '2025-01-01',
                        'monthly_gross_salary' => $gross,
                        'annual_ctc' => $gross * 12,
                        'status' => 'active',
                    ]);
                }
            }

            $this->command->info('Initialized salary structures and leave balances.');

            // 3. Generate Public Holidays (March 1, 2026 to June 12, 2026)
            $nationalHolidayType = HolidayType::where('code', 'national')->first();
            if (!$nationalHolidayType) {
                $nationalHolidayType = HolidayType::create([
                    'name' => 'National Holiday',
                    'code' => 'national',
                    'status' => 'active',
                ]);
            }

            $holidaysList = [
                ['name' => 'Holi Festival', 'date' => '2026-03-03', 'code' => 'HOLI-2026'],
                ['name' => 'Good Friday', 'date' => '2026-04-03', 'code' => 'GFRI-2026'],
                ['name' => 'Eid al-Fitr', 'date' => '2026-03-20', 'code' => 'EID-2026'],
                ['name' => 'International Workers Day', 'date' => '2026-05-01', 'code' => 'MAYD-2026'],
            ];

            $allLocations = Location::all();
            $holidayDates = [];

            foreach ($holidaysList as $h) {
                $holiday = Holiday::firstOrCreate(
                    ['holiday_code' => $h['code']],
                    [
                        'holiday_name' => $h['name'],
                        'holiday_date' => $h['date'],
                        'holiday_type_id' => $nationalHolidayType->id,
                        'description' => $h['name'] . ' holiday observance.',
                        'is_paid' => true,
                        'status' => 'published',
                        'publish_at' => now(),
                        'created_by' => 1,
                    ]
                );

                // Map holiday to all locations
                $holiday->locations()->sync($allLocations->pluck('id')->toArray());
                $holidayDates[$h['date']] = true;
            }

            $this->command->info('Generated public holidays.');

            // 4. Generate Mapped Leave Requests (March 1, 2026 to June 12, 2026)
            $leaveRequestDates = []; // Mapped by [employee_id][date] = true
            $adminUser = User::find(1);

            foreach ($employees as $emp) {
                $leaveRequestDates[$emp->id] = [];
                // Generate 1-2 random leave requests for each employee
                $numLeaves = rand(1, 2);
                for ($i = 0; $i < $numLeaves; $i++) {
                    $leaveType = $leaveTypes->random();
                    
                    // Pick a random month (March: 3, April: 4, May: 5)
                    $month = rand(3, 5);
                    // Find a random weekday in that month
                    $day = rand(5, 25);
                    $startDateString = "2026-0{$month}-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                    $startDate = Carbon::parse($startDateString);
                    
                    // Skip weekends or holidays
                    if ($startDate->isWeekend() || isset($holidayDates[$startDateString])) {
                        continue;
                    }

                    $duration = rand(1, 2);
                    $endDate = $startDate->copy()->addDays($duration - 1);
                    
                    // Generate dates list
                    $dates = [];
                    $isValid = true;
                    for ($d = 0; $d < $duration; $d++) {
                        $currDate = $startDate->copy()->addDays($d);
                        $currDateStr = $currDate->toDateString();
                        if ($currDate->isWeekend() || isset($holidayDates[$currDateStr]) || isset($leaveRequestDates[$emp->id][$currDateStr])) {
                            $isValid = false;
                            break;
                        }
                        $dates[] = $currDateStr;
                    }

                    if (!$isValid) {
                        continue;
                    }

                    // Create Leave Request
                    $leaveRequest = LeaveRequest::create([
                        'employee_id' => $emp->id,
                        'leave_type_id' => $leaveType->id,
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $endDate->toDateString(),
                        'total_days' => $duration,
                        'half_day' => false,
                        'reason' => 'Personal work / health leave.',
                        'emergency_phone' => $emp->employeeDetail->phone ?? '1234567890',
                        'status' => 'approved',
                        'applied_at' => $startDate->copy()->subDays(2)->toDateTimeString(),
                        'approved_by' => $adminUser->id,
                        'approved_at' => $startDate->copy()->subDays(1)->toDateTimeString(),
                    ]);

                    // Create Days
                    foreach ($dates as $dateStr) {
                        LeaveRequestDay::create([
                            'leave_request_id' => $leaveRequest->id,
                            'leave_date' => $dateStr,
                            'day_weight' => 1.0,
                            'session' => 'full',
                        ]);
                        $leaveRequestDates[$emp->id][$dateStr] = true;
                    }

                    // Update used balance
                    $bal = LeaveBalance::where('employee_id', $emp->id)->where('leave_type_id', $leaveType->id)->first();
                    if ($bal) {
                        $bal->used_balance += $duration;
                        $bal->save();
                    }
                }
            }

            $this->command->info('Generated approved employee leave requests.');

            // 5. Daily Attendance Loop (March 1, 2026 to June 12, 2026)
            $start = Carbon::parse('2026-03-01');
            $end = Carbon::parse('2026-06-12');
            
            $daysCount = $start->diffInDays($end) + 1;
            $this->command->info("Generating daily attendance records for {$daysCount} calendar days...");

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $dateStr = $date->toDateString();
                $isWeekend = $date->isWeekend();
                $isHoliday = isset($holidayDates[$dateStr]);

                foreach ($employees as $emp) {
                    $shift = $emp->employeeDetail->shift ?? Shift::first();
                    $status = 'Absent';
                    $clockIn = null;
                    $clockOut = null;
                    $workedHours = 0.00;
                    $lateMin = 0;
                    $earlyExitMin = 0;
                    $otMin = 0;

                    if ($isWeekend) {
                        $status = 'Weekly Off';
                    } elseif ($isHoliday) {
                        $status = 'Holiday';
                    } elseif (isset($leaveRequestDates[$emp->id][$dateStr])) {
                        $status = 'On Leave';
                    } else {
                        // Standard working day: present, WFH, absent
                        $rand = rand(1, 100);
                        if ($rand <= 88) {
                            $status = 'Present';
                        } elseif ($rand <= 95) {
                            $status = 'Late';
                        } elseif ($rand <= 98) {
                            $status = 'Work From Home';
                        } else {
                            $status = 'Absent';
                        }

                        if ($status === 'Present' || $status === 'Late' || $status === 'Work From Home') {
                            $shiftStart = Carbon::parse($shift->start_time ?? '09:00:00');
                            $shiftEnd = Carbon::parse($shift->end_time ?? '18:00:00');
                            $grace = $shift->grace_period_minutes ?? 15;

                            if ($status === 'Work From Home') {
                                $workedHours = 8.00;
                                $clockIn = Carbon::parse($dateStr . ' ' . $shiftStart->toTimeString());
                                $clockOut = Carbon::parse($dateStr . ' ' . $shiftEnd->toTimeString());
                            } else {
                                // Present / Late: generate realistic clock timings
                                if ($status === 'Present') {
                                    // Clock-in between 15 mins before to grace mins after
                                    $inMinOffset = rand(-15, $grace);
                                    $clockIn = Carbon::parse($dateStr . ' ' . $shiftStart->copy()->addMinutes($inMinOffset)->toTimeString());
                                } else {
                                    // Late: clock-in between grace+1 mins to 60 mins after
                                    $inMinOffset = rand($grace + 1, 60);
                                    $clockIn = Carbon::parse($dateStr . ' ' . $shiftStart->copy()->addMinutes($inMinOffset)->toTimeString());
                                    $lateMin = $inMinOffset;
                                }

                                // Clock-out: generate clock-out between 15 mins before to 45 mins after shiftEnd
                                $outMinOffset = rand(-15, 45);
                                $clockOut = Carbon::parse($dateStr . ' ' . $shiftEnd->copy()->addMinutes($outMinOffset)->toTimeString());

                                if ($outMinOffset < 0) {
                                    $earlyExitMin = abs($outMinOffset);
                                } elseif ($outMinOffset > 0) {
                                    $otMin = $outMinOffset;
                                }

                                // Compute worked hours
                                $workedHours = round($clockIn->diffInMinutes($clockOut) / 60, 2);
                            }
                        }
                    }

                    // Create Attendance Record
                    $attendance = Attendance::create([
                        'user_id' => $emp->id,
                        'attendance_date' => $dateStr,
                        'shift_id' => $shift->id ?? null,
                        'clock_in' => $clockIn ? $clockIn->toDateTimeString() : null,
                        'clock_out' => $clockOut ? $clockOut->toDateTimeString() : null,
                        'worked_hours' => $workedHours,
                        'late_minutes' => $lateMin,
                        'early_exit_minutes' => $earlyExitMin,
                        'overtime_minutes' => $otMin,
                        'attendance_status' => $status,
                        'remarks' => $status === 'Present' ? 'Completed standard shift.' : ($status === 'Late' ? 'Late arrival.' : null),
                    ]);

                    // Generate logs for Clock In / Clock Out
                    if ($clockIn) {
                        AttendanceLog::create([
                            'attendance_id' => $attendance->id,
                            'user_id' => $emp->id,
                            'type' => 'clock_in',
                            'log_time' => $clockIn->toDateTimeString(),
                            'ip_address' => '192.168.1.' . rand(2, 254),
                            'device_info' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                            'method' => 'web',
                        ]);
                    }
                    if ($clockOut) {
                        AttendanceLog::create([
                            'attendance_id' => $attendance->id,
                            'user_id' => $emp->id,
                            'type' => 'clock_out',
                            'log_time' => $clockOut->toDateTimeString(),
                            'ip_address' => '192.168.1.' . rand(2, 254),
                            'device_info' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                            'method' => 'web',
                        ]);
                    }
                }
            }

            $this->command->info('Generated daily attendance and clock logs.');

            // 6. Monthly Summaries Aggregation (March, April, May, June 2026)
            $months = [
                ['month' => 3, 'year' => 2026],
                ['month' => 4, 'year' => 2026],
                ['month' => 5, 'year' => 2026],
                ['month' => 6, 'year' => 2026],
            ];

            foreach ($months as $m) {
                foreach ($employees as $emp) {
                    $attendancesQuery = Attendance::where('user_id', $emp->id)
                        ->whereMonth('attendance_date', $m['month'])
                        ->whereYear('attendance_date', $m['year']);

                    $counts = $attendancesQuery->select('attendance_status', DB::raw('count(*) as count'))
                        ->groupBy('attendance_status')
                        ->pluck('count', 'attendance_status')
                        ->toArray();

                    $totalWorkedHours = $attendancesQuery->sum('worked_hours');
                    $totalOvertimeMinutes = $attendancesQuery->sum('overtime_minutes');

                    AttendanceMonthlySummary::create([
                        'user_id' => $emp->id,
                        'month' => $m['month'],
                        'year' => $m['year'],
                        'present_days' => $counts['Present'] ?? 0,
                        'absent_days' => $counts['Absent'] ?? 0,
                        'late_days' => $counts['Late'] ?? 0,
                        'leave_days' => $counts['On Leave'] ?? 0,
                        'holiday_days' => $counts['Holiday'] ?? 0,
                        'wfh_days' => $counts['Work From Home'] ?? 0,
                        'missed_punch_days' => $counts['Missed Punch'] ?? 0,
                        'total_working_hours' => $totalWorkedHours,
                        'total_overtime_hours' => round($totalOvertimeMinutes / 60, 2),
                    ]);
                }
            }

            $this->command->info('Generated monthly attendance summaries.');

            // 7. Payroll Runs & Payslips (March, April, May 2026)
            $payrollMonths = [
                ['month' => 3, 'year' => 2026],
                ['month' => 4, 'year' => 2026],
                ['month' => 5, 'year' => 2026],
            ];

            $salaryComponents = SalaryComponent::where('status', 'active')->get()->keyBy('component_code');

            foreach ($payrollMonths as $pm) {
                // Determine calendar days in that month
                $daysInMonth = Carbon::createFromDate($pm['year'], $pm['month'], 1)->daysInMonth;

                // Create a PayrollRun record
                $payrollRun = PayrollRun::create([
                    'run_month' => $pm['month'],
                    'run_year' => $pm['year'],
                    'run_type' => 'monthly',
                    'status' => 'published',
                    'processed_by' => 1,
                    'processed_at' => Carbon::createFromDate($pm['year'], $pm['month'], $daysInMonth)->addDay()->toDateTimeString(),
                    'approved_by' => 1,
                    'approved_at' => Carbon::createFromDate($pm['year'], $pm['month'], $daysInMonth)->addDay()->toDateTimeString(),
                    'total_employees' => 0,
                    'total_gross' => 0.00,
                    'total_earnings' => 0.00,
                    'total_deductions' => 0.00,
                    'total_net' => 0.00,
                ]);

                $runGrossTotal = 0;
                $runEarningsTotal = 0;
                $runDeductionsTotal = 0;
                $runNetTotal = 0;
                $empCount = 0;

                foreach ($employees as $emp) {
                    $salStructure = EmployeeSalaryStructure::where('employee_id', $emp->id)->where('status', 'active')->first();
                    if (!$salStructure) {
                        continue;
                    }

                    $monthlyGross = $salStructure->monthly_gross_salary;

                    // Get LOP days from Absent days in attendance
                    $absentDays = Attendance::where('user_id', $emp->id)
                        ->whereMonth('attendance_date', $pm['month'])
                        ->whereYear('attendance_date', $pm['year'])
                        ->where('attendance_status', 'Absent')
                        ->count();

                    $lopDays = $absentDays;
                    $paidDays = $daysInMonth - $lopDays;

                    // Calculate earned gross
                    $earnedGross = round(($monthlyGross / $daysInMonth) * $paidDays, 2);

                    // Compute salary items
                    $basic = round($earnedGross * 0.50, 2);
                    $hra = round($basic * 0.50, 2);
                    $special = round($earnedGross - ($basic + $hra), 2);
                    $pf = round($basic * 0.12, 2);
                    $esi = round($earnedGross * 0.0075, 2);
                    $pt = $earnedGross > 20000 ? 200.00 : 0.00;

                    $totalEarnings = $basic + $hra + $special;
                    $totalDeductions = $pf + $esi + $pt;
                    $netSalary = $totalEarnings - $totalDeductions;

                    // Create PayrollRunEmployee
                    $runEmployee = PayrollRunEmployee::create([
                        'payroll_run_id' => $payrollRun->id,
                        'employee_id' => $emp->id,
                        'salary_structure_id' => $standardStructure->id,
                        'monthly_gross_salary' => $monthlyGross,
                        'total_working_days' => $daysInMonth,
                        'paid_days' => $paidDays,
                        'lop_days' => $lopDays,
                        'gross_salary_earned' => $earnedGross,
                        'total_earnings' => $totalEarnings,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'status' => 'published',
                    ]);

                    // Add items mapping
                    $items = [
                        ['code' => 'BASIC', 'amount' => $basic, 'name' => 'Basic Salary', 'type' => 'earning'],
                        ['code' => 'HRA', 'amount' => $hra, 'name' => 'House Rent Allowance', 'type' => 'earning'],
                        ['code' => 'SPECIAL_ALLOWANCE', 'amount' => $special, 'name' => 'Special Allowance', 'type' => 'earning'],
                        ['code' => 'PF', 'amount' => $pf, 'name' => 'Provident Fund', 'type' => 'deduction'],
                        ['code' => 'ESI', 'amount' => $esi, 'name' => 'Employee State Insurance', 'type' => 'deduction'],
                    ];
                    if ($pt > 0) {
                        $items[] = ['code' => 'PT', 'amount' => $pt, 'name' => 'Professional Tax', 'type' => 'deduction'];
                    }

                    foreach ($items as $item) {
                        $comp = $salaryComponents->get($item['code']);
                        PayrollItem::create([
                            'payroll_run_employee_id' => $runEmployee->id,
                            'salary_component_id' => $comp->id ?? 1,
                            'component_name' => $item['name'],
                            'component_code' => $item['code'],
                            'component_type' => $item['type'],
                            'amount' => $item['amount'],
                        ]);
                    }

                    // Create Payslip
                    Payslip::create([
                        'payroll_run_employee_id' => $runEmployee->id,
                        'employee_id' => $emp->id,
                        'reference_no' => 'PAY-' . $pm['year'] . str_pad($pm['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($emp->id, 4, '0', STR_PAD_LEFT),
                        'gross_salary' => $earnedGross,
                        'total_earnings' => $totalEarnings,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'pdf_path' => null,
                        'secure_hash' => Str::random(40),
                        'generated_at' => Carbon::now()->toDateTimeString(),
                        'published_at' => Carbon::now()->toDateTimeString(),
                    ]);

                    // Accumulate totals
                    $runGrossTotal += $earnedGross;
                    $runEarningsTotal += $totalEarnings;
                    $runDeductionsTotal += $totalDeductions;
                    $runNetTotal += $netSalary;
                    $empCount++;
                }

                // Update payroll run details
                $payrollRun->update([
                    'total_employees' => $empCount,
                    'total_gross' => $runGrossTotal,
                    'total_earnings' => $runEarningsTotal,
                    'total_deductions' => $runDeductionsTotal,
                    'total_net' => $runNetTotal,
                ]);
            }

            $this->command->info('Generated payroll runs and payslips.');
            $this->command->info('ThreeMonthDataSeeder completed successfully!');
        });
    }
}
