<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\EmployeeSalaryStructure;
use App\Models\SalaryRevision;
use App\Models\PayrollRun;
use App\Models\PayrollRunEmployee;
use App\Models\EmployeeLoan;
use App\Models\LoanRepayment;
use App\Models\SalaryAdvance;
use App\Models\Attendance;
use App\Models\LeaveType;
use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Services\PayrollCalculationEngine;
use App\Services\PayrollService;
use App\Services\SalaryRevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

class PayrollManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $employee;
    protected $structure;
    protected $components;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        // Create standard location, shift, department, designation
        $location = Location::create([
            'location_name' => 'HQ Noida',
            'location_code' => 'HQ-ND',
            'status' => 'active',
        ]);

        $shift = Shift::create([
            'shift_name' => 'Standard',
            'shift_code' => 'STD',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'grace_period_minutes' => 15,
            'break_minutes' => 60,
        ]);

        $department = Department::create([
            'department_name' => 'Engineering',
            'department_code' => 'ENG',
            'status' => 'active',
        ]);

        $designation = Designation::create([
            'designation_name' => 'Software Engineer',
            'designation_code' => 'SWE',
            'level' => 5,
            'status' => 'active',
        ]);

        // Create users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->admin->assignRole('Admin');

        $this->employee = User::create([
            'name' => 'John Dev',
            'email' => 'john.dev@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->employee->assignRole('Employee');

        DB::table('employee_details')->insert([
            'user_id' => $this->employee->id,
            'employee_code' => 'EMP-101',
            'joining_date' => '2026-01-01',
            'location_id' => $location->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'shift_id' => $shift->id,
        ]);

        // Create components
        $components = [
            'BASIC' => SalaryComponent::create([
                'component_name' => 'Basic Salary',
                'component_code' => 'BASIC',
                'component_type' => 'earning',
                'calculation_type' => 'percentage_of_gross',
                'default_value' => 50.00,
            ]),
            'HRA' => SalaryComponent::create([
                'component_name' => 'House Rent Allowance',
                'component_code' => 'HRA',
                'component_type' => 'earning',
                'calculation_type' => 'percentage_of_basic',
                'default_value' => 50.00,
            ]),
            'SPECIAL_ALLOWANCE' => SalaryComponent::create([
                'component_name' => 'Special Allowance',
                'component_code' => 'SPECIAL_ALLOWANCE',
                'component_type' => 'earning',
                'calculation_type' => 'percentage_of_gross',
                'default_value' => 25.00,
            ]),
            'PF' => SalaryComponent::create([
                'component_name' => 'Provident Fund',
                'component_code' => 'PF',
                'component_type' => 'deduction',
                'calculation_type' => 'percentage_of_basic',
                'default_value' => 12.00,
            ]),
            'PT' => SalaryComponent::create([
                'component_name' => 'Professional Tax',
                'component_code' => 'PT',
                'component_type' => 'deduction',
                'calculation_type' => 'custom_formula',
                'default_value' => 0.00,
            ]),
            'LOAN_RECOVERY' => SalaryComponent::create([
                'component_name' => 'Loan Recovery',
                'component_code' => 'LOAN_RECOVERY',
                'component_type' => 'deduction',
                'calculation_type' => 'custom_formula',
                'default_value' => 0.00,
            ]),
            'ADVANCE_RECOVERY' => SalaryComponent::create([
                'component_name' => 'Advance Recovery',
                'component_code' => 'ADVANCE_RECOVERY',
                'component_type' => 'deduction',
                'calculation_type' => 'custom_formula',
                'default_value' => 0.00,
            ]),
            'OVERTIME' => SalaryComponent::create([
                'component_name' => 'Overtime Allowance',
                'component_code' => 'OVERTIME',
                'component_type' => 'earning',
                'calculation_type' => 'custom_formula',
                'default_value' => 0.00,
            ]),
        ];

        $this->components = $components;

        // Create structure
        $this->structure = SalaryStructure::create([
            'name' => 'Standard Structure',
            'description' => 'Test standard structure',
            'status' => 'active',
        ]);

        $this->structure->components()->attach([
            $components['BASIC']->id => ['calculation_value' => 50.00, 'sort_order' => 1],
            $components['HRA']->id => ['calculation_value' => 50.00, 'sort_order' => 2],
            $components['SPECIAL_ALLOWANCE']->id => ['calculation_value' => 25.00, 'sort_order' => 3],
            $components['PF']->id => ['calculation_value' => 12.00, 'sort_order' => 4],
            $components['PT']->id => ['calculation_value' => 0.00, 'sort_order' => 5],
            $components['LOAN_RECOVERY']->id => ['calculation_value' => 0.00, 'sort_order' => 6],
            $components['ADVANCE_RECOVERY']->id => ['calculation_value' => 0.00, 'sort_order' => 7],
            $components['OVERTIME']->id => ['calculation_value' => 0.00, 'sort_order' => 8],
        ]);

        // Assign structure to employee
        EmployeeSalaryStructure::create([
            'employee_id' => $this->employee->id,
            'salary_structure_id' => $this->structure->id,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
            'monthly_gross_salary' => 50000.00,
            'annual_ctc' => 600000.00,
            'status' => 'active',
        ]);
    }

    public function test_salary_calculation_math()
    {
        $engine = app(PayrollCalculationEngine::class);

        // Calculate for June 2026 (30 days), no unpaid leave, no absents, monthly gross = 50000.00
        $result = $engine->calculate($this->employee, 6, 2026, 50000.00, $this->structure);

        $this->assertEquals(30, $result['total_working_days']);
        $this->assertEquals(30, $result['paid_days']);
        $this->assertEquals(0, $result['lop_days']);

        // BASIC should be 50% of gross = 25000
        $this->assertEquals(25000.00, $result['items']['BASIC']['amount']);
        // HRA should be 50% of Basic = 12500
        $this->assertEquals(12500.00, $result['items']['HRA']['amount']);
        // SPECIAL_ALLOWANCE should be 25% of gross = 12500
        $this->assertEquals(12500.00, $result['items']['SPECIAL_ALLOWANCE']['amount']);

        // PF should be 12% of Basic = 3000
        $this->assertEquals(3000.00, $result['items']['PF']['amount']);
        // PT should be 200 (since gross salary is >= 15000)
        $this->assertEquals(200.00, $result['items']['PT']['amount']);

        // Gross salary earned = 50000
        $this->assertEquals(50000.00, $result['gross_salary_earned']);
        // Net salary = Earnings (50000) - Deductions (3000 + 200) = 46800
        $this->assertEquals(46800.00, $result['net_salary']);
    }

    public function test_loss_of_pay_proration()
    {
        // Set up unpaid leave for employee
        $leaveType = LeaveType::create([
            'name' => 'LWP',
            'code' => 'LWP',
            'is_paid' => false,
            'status' => 'active',
        ]);

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-06-08',
            'end_date' => '2026-06-09',
            'total_days' => 2,
            'status' => 'approved',
            'reason' => 'Personal work',
            'emergency_phone' => '1234567890',
        ]);

        LeaveRequestDay::create([
            'leave_request_id' => $leaveRequest->id,
            'leave_date' => '2026-06-08',
            'day_weight' => 1.0,
        ]);
        LeaveRequestDay::create([
            'leave_request_id' => $leaveRequest->id,
            'leave_date' => '2026-06-09',
            'day_weight' => 1.0,
        ]);

        // Add 1 Absent Day
        Attendance::create([
            'user_id' => $this->employee->id,
            'attendance_date' => '2026-06-15',
            'attendance_status' => 'Absent',
            'shift_id' => 1,
            'clock_in' => null,
            'clock_out' => null,
        ]);

        $engine = app(PayrollCalculationEngine::class);

        // Total LOP days should be 2 + 1 = 3 days. Total working days in June 2026 = 30.
        // Paid days = 27 days. Proration ratio = 27 / 30 = 0.9
        $result = $engine->calculate($this->employee, 6, 2026, 50000.00, $this->structure);

        $this->assertEquals(30, $result['total_working_days']);
        $this->assertEquals(27, $result['paid_days']);
        $this->assertEquals(3, $result['lop_days']);

        // Basic: 25000 * 0.9 = 22500
        $this->assertEquals(22500.00, $result['items']['BASIC']['amount']);
        // HRA: 12500 * 0.9 = 11250
        $this->assertEquals(11250.00, $result['items']['HRA']['amount']);
        // SPECIAL_ALLOWANCE: 12500 * 0.9 = 11250
        $this->assertEquals(11250.00, $result['items']['SPECIAL_ALLOWANCE']['amount']);

        // Earnings total = 22500 + 11250 + 11250 = 45000
        $this->assertEquals(45000.00, $result['gross_salary_earned']);
    }

    public function test_overtime_calculation()
    {
        // Add 240 minutes (4 hours) overtime in June
        Attendance::create([
            'user_id' => $this->employee->id,
            'attendance_date' => '2026-06-02',
            'attendance_status' => 'Present',
            'shift_id' => 1,
            'clock_in' => '09:00',
            'clock_out' => '22:00',
            'overtime_minutes' => 240,
        ]);

        $engine = app(PayrollCalculationEngine::class);

        $result = $engine->calculate($this->employee, 6, 2026, 50000.00, $this->structure);

        // Overtime Hours = 4 hours. Gross = 50000
        // Overtime rate per hour = (50000 / (22 * 8)) = 284.09
        // Amount = 4 * 284.09 * 1.5 = 1704.55
        $this->assertArrayHasKey('OVERTIME', $result['items']);
        $this->assertEquals(1704.55, $result['items']['OVERTIME']['amount']);
    }

    public function test_loan_and_advance_recovery_publishing()
    {
        // Create active loan
        $loan = EmployeeLoan::create([
            'employee_id' => $this->employee->id,
            'principal_amount' => 12000.00,
            'remaining_principal' => 12000.00,
            'monthly_emi' => 2000.00,
            'disbursal_date' => '2026-01-01',
            'status' => 'active',
        ]);

        // Create approved advance for June 2026 recovery
        $advance = SalaryAdvance::create([
            'employee_id' => $this->employee->id,
            'amount' => 1500.00,
            'request_date' => '2026-06-01',
            'recovery_month' => 6,
            'recovery_year' => 2026,
            'status' => 'approved',
        ]);

        // Start payroll run for June 2026
        $payrollService = app(PayrollService::class);
        $run = $payrollService->startPayrollRun(6, 2026, $this->admin->id);

        // Run process job synchronously
        $job = new \App\Jobs\ProcessPayrollRunJob($run->id);
        $job->handle(app(PayrollCalculationEngine::class));

        $run->refresh();
        $this->assertEquals('calculated', $run->status);

        // Verify deductions are calculated
        $runEmployee = PayrollRunEmployee::where('payroll_run_id', $run->id)
            ->where('employee_id', $this->employee->id)
            ->first();

        // Loan recovery item should exist with amount 2000
        $this->assertDatabaseHas('payroll_items', [
            'payroll_run_employee_id' => $runEmployee->id,
            'component_code' => 'LOAN_RECOVERY',
            'amount' => 2000.00,
        ]);

        // Advance recovery item should exist with amount 1500
        $this->assertDatabaseHas('payroll_items', [
            'payroll_run_employee_id' => $runEmployee->id,
            'component_code' => 'ADVANCE_RECOVERY',
            'amount' => 1500.00,
        ]);

        // Loan and advance balances should NOT change while in draft/calculated status
        $loan->refresh();
        $this->assertEquals(12000.00, $loan->remaining_principal);
        $advance->refresh();
        $this->assertEquals('approved', $advance->status);

        // Perform Approvals
        $payrollService->approveRun($run->id, $this->admin->id, 'Finance', 'Finance ok');
        $payrollService->approveRun($run->id, $this->admin->id, 'HR', 'HR ok');

        $run->refresh();
        $this->assertEquals('approved', $run->status);

        // Publish Run
        $payrollService->publishRun($run->id, $this->admin->id);

        $run->refresh();
        $this->assertEquals('published', $run->status);

        // Loan remaining balance should reduce
        $loan->refresh();
        $this->assertEquals(10000.00, $loan->remaining_principal);

        // Repayment log should be recorded
        $this->assertDatabaseHas('loan_repayments', [
            'employee_loan_id' => $loan->id,
            'payroll_run_employee_id' => $runEmployee->id,
            'amount' => 2000.00,
        ]);

        // Advance should be marked as recovered
        $advance->refresh();
        $this->assertEquals('recovered', $advance->status);
    }

    public function test_salary_revision_proposal_and_approval()
    {
        $revisionService = app(SalaryRevisionService::class);

        // Propose a revision to 60,000 gross
        $revision = $revisionService->proposeRevision(
            $this->employee->id,
            60000.00,
            '2026-07-01',
            'Performance Hike'
        );

        $this->assertDatabaseHas('salary_revisions', [
            'employee_id' => $this->employee->id,
            'new_gross_salary' => 60000.00,
            'approved_by' => null,
        ]);

        // Verify active structure is still original
        $activeStructure = EmployeeSalaryStructure::where('employee_id', $this->employee->id)
            ->where('status', 'active')
            ->first();
        $this->assertEquals(50000.00, $activeStructure->monthly_gross_salary);

        // Approve revision
        $revisionService->approveRevision($revision->id, $this->admin->id, $this->structure->id);

        // Verify revision is marked approved
        $revision->refresh();
        $this->assertEquals($this->admin->id, $revision->approved_by);

        // Verify previous structure is deactivated and effective_to is 2026-06-30
        $activeStructure->refresh();
        $this->assertEquals('inactive', $activeStructure->status);
        $this->assertEquals('2026-06-30', $activeStructure->effective_to->toDateString());

        // Verify new structure is active and starts 2026-07-01
        $newActiveStructure = EmployeeSalaryStructure::where('employee_id', $this->employee->id)
            ->where('status', 'active')
            ->first();
        $this->assertNotNull($newActiveStructure);
        $this->assertEquals(60000.00, $newActiveStructure->monthly_gross_salary);
        $this->assertEquals('2026-07-01', $newActiveStructure->effective_from->toDateString());
    }

    public function test_multilevel_approvals_enforcement()
    {
        $payrollService = app(PayrollService::class);
        $run = $payrollService->startPayrollRun(6, 2026, $this->admin->id);

        // Run process job synchronously
        $job = new \App\Jobs\ProcessPayrollRunJob($run->id);
        $job->handle(app(PayrollCalculationEngine::class));

        // Approving HR before Finance should throw Exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Finance approval is required before HR approval.");

        $payrollService->approveRun($run->id, $this->admin->id, 'HR', 'HR review');
    }
}
