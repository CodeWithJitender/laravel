<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyRule;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\LeaveAccrual;
use App\Models\LeaveCarryForward;
use App\Services\LeaveBalanceService;
use App\Services\LeaveRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $location;
    protected $shift;
    protected $department;
    protected $designation;
    protected $employee;
    protected $manager;
    protected $leaveType;
    protected $leavePolicy;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Spatie roles/permissions
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->location = Location::create([
            'location_name' => 'Noida HQ',
            'location_code' => 'ND-01',
            'status' => 'active',
        ]);

        $this->shift = Shift::create([
            'shift_name' => 'General',
            'shift_code' => 'GEN',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'grace_period_minutes' => 15,
            'break_minutes' => 60,
            'status' => 'active',
            'weekly_off' => ['Saturday', 'Sunday'],
        ]);

        $this->department = Department::create([
            'department_name' => 'Engineering',
            'department_code' => 'ENG',
            'status' => 'active',
        ]);

        $this->designation = Designation::create([
            'designation_name' => 'Software Engineer',
            'designation_code' => 'SWE',
            'level' => 5,
            'status' => 'active',
        ]);

        // Create Manager
        $this->manager = User::create([
            'name' => 'Jane Manager',
            'email' => 'manager@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->manager->assignRole('Manager');

        DB::table('employee_details')->insert([
            'user_id' => $this->manager->id,
            'employee_code' => 'MGR-100',
            'joining_date' => '2026-01-01',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'manager_id' => null,
            'gender' => 'female',
        ]);

        // Create Employee under the Manager
        $this->employee = User::create([
            'name' => 'John Employee',
            'email' => 'employee@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->employee->assignRole('Employee');

        DB::table('employee_details')->insert([
            'user_id' => $this->employee->id,
            'employee_code' => 'EMP-200',
            'joining_date' => '2026-01-02',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'manager_id' => $this->manager->id,
            'gender' => 'male',
        ]);

        // Create leave type and policy
        $this->leaveType = LeaveType::create([
            'name' => 'Casual Leave',
            'code' => 'CL',
            'color' => '#6366f1',
            'is_paid' => true,
            'status' => 'active',
        ]);

        $this->leavePolicy = LeavePolicy::create([
            'leave_type_id' => $this->leaveType->id,
            'annual_allocation' => 12.00,
            'monthly_accrual' => false,
            'carry_forward_limit' => 5.00,
            'max_consecutive_days' => 15,
            'notice_period_days' => 2,
            'status' => 'active',
        ]);

        // Initialize Employee Balances
        app(LeaveBalanceService::class)->initializeEmployeeBalances($this->employee);
    }

    public function test_employee_cannot_exceed_remaining_balance()
    {
        $this->actingAs($this->employee);

        // Employee has 12 days allocated. Requesting a 13-day period (excluding weekends)
        // 2026-07-06 (Mon) to 2026-07-22 (Wed) spans 13 working days.
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Insufficient leave balance.");

        app(LeaveRequestService::class)->submitRequest($this->employee, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-07-06',
            'end_date' => '2026-07-22',
            'half_day' => false,
            'reason' => 'Extended vacation request',
            'emergency_phone' => '1234567890',
        ]);
    }

    public function test_employee_cannot_submit_overlapping_requests()
    {
        $this->actingAs($this->employee);

        // Submit first request successfully (2026-07-06 to 2026-07-08 = 3 days)
        app(LeaveRequestService::class)->submitRequest($this->employee, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-07-06',
            'end_date' => '2026-07-08',
            'half_day' => false,
            'reason' => 'First trip',
            'emergency_phone' => '1234567890',
        ]);

        // Submit overlapping request (should fail)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("You have already requested or been approved for leave during this period.");

        app(LeaveRequestService::class)->submitRequest($this->employee, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-07-07',
            'end_date' => '2026-07-09',
            'half_day' => false,
            'reason' => 'Second trip overlapping',
            'emergency_phone' => '1234567890',
        ]);
    }

    public function test_policy_notice_period_days_blocks_application()
    {
        // Set mock system date to 2026-06-04. Notice period is 2 days.
        // Applying for 2026-06-05 should fail notice constraint.
        Carbon::setTestNow(Carbon::parse('2026-06-04 10:00:00'));
        $this->actingAs($this->employee);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("This leave type requires a notice period of at least 2 days.");

        app(LeaveRequestService::class)->submitRequest($this->employee, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-05',
            'half_day' => false,
            'reason' => 'Immediate leave request',
            'emergency_phone' => '1234567890',
        ]);
    }

    public function test_manager_approval_deducts_balance_and_updates_attendance()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->employee);
        
        // Submit request for 2026-07-06 (Mon) to 2026-07-06 (Mon) = 1 day
        $request = app(LeaveRequestService::class)->submitRequest($this->employee, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-07-06',
            'end_date' => '2026-07-06',
            'half_day' => false,
            'reason' => 'Doctor appointment',
            'emergency_phone' => '1234567890',
        ]);

        $this->assertEquals('pending', $request->status);

        // Check balance sheet before approval (allocated = 12, pending = 1, used = 0)
        $balance = LeaveBalance::where('employee_id', $this->employee->id)->where('leave_type_id', $this->leaveType->id)->first();
        $this->assertEquals(12.00, $balance->allocated_balance);
        $this->assertEquals(1.00, $balance->pending_balance);
        $this->assertEquals(0.00, $balance->used_balance);

        // Approve request as Manager
        $this->actingAs($this->manager);
        app(LeaveRequestService::class)->approveRequest($request->id, $this->manager->id, 'Approved.');

        $request->refresh();
        $this->assertEquals('approved', $request->status);

        // Check balance sheet after approval (allocated = 12, pending = 0, used = 1)
        $balance->refresh();
        $this->assertEquals(12.00, $balance->allocated_balance);
        $this->assertEquals(0.00, $balance->pending_balance);
        $this->assertEquals(1.00, $balance->used_balance);
        $this->assertEquals(11.00, $balance->remaining_balance);

        // Check attendance record updated on 2026-07-06
        $attendance = Attendance::where('user_id', $this->employee->id)->whereDate('attendance_date', '2026-07-06')->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('On Leave', $attendance->attendance_status);
        $this->assertEquals('Approved Leave: Casual Leave', $attendance->remarks);
    }

    public function test_gender_demographic_rule_denies_eligibility()
    {
        // Add gender rule: female only
        LeavePolicyRule::create([
            'policy_id' => $this->leavePolicy->id,
            'rule_type' => 'gender',
            'rule_operator' => 'in',
            'rule_values' => ['female'],
        ]);

        $this->actingAs($this->employee);

        // Employee is male. Tries to apply. Should fail demographic eligibility.
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("You are not eligible to apply for this leave type based on organization rules.");

        app(LeaveRequestService::class)->submitRequest($this->employee, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-07-06',
            'end_date' => '2026-07-06',
            'half_day' => false,
            'reason' => 'Restricted leave category test',
            'emergency_phone' => '1234567890',
        ]);
    }

    public function test_accrual_command_accrues_correct_proportion()
    {
        // Create an accrued leave type (e.g. Sick Leave, monthly accrual = true)
        $sickLeave = LeaveType::create([
            'name' => 'Sick Leave',
            'code' => 'SL',
            'color' => '#ef4444',
            'is_paid' => true,
            'status' => 'active',
        ]);

        $sickPolicy = LeavePolicy::create([
            'leave_type_id' => $sickLeave->id,
            'annual_allocation' => 12.00,
            'monthly_accrual' => true,
            'carry_forward_limit' => 0.00,
            'max_consecutive_days' => null,
            'notice_period_days' => 0,
            'status' => 'active',
        ]);

        // Initialize balances for the employee
        app(LeaveBalanceService::class)->initializeEmployeeBalances($this->employee);

        // The initial accrued_balance should be 0.00
        $balance = LeaveBalance::where('employee_id', $this->employee->id)->where('leave_type_id', $sickLeave->id)->first();
        $this->assertEquals(0.00, $balance->accrued_balance);

        // Run monthly accruals command
        $this->artisan('leave:accrue')
             ->assertSuccessful();

        // 12.00 / 12 = 1.00 day accrued
        $balance->refresh();
        $this->assertEquals(1.00, $balance->accrued_balance);
        $this->assertEquals(1.00, $balance->remaining_balance);

        // Check audit log
        $this->assertDatabaseHas('leave_accruals', [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $sickLeave->id,
            'accrued_amount' => 1.00,
        ]);
    }

    public function test_carry_forward_command_calculates_and_applies_limits()
    {
        // Manually adjust the balance of the employee before carry forward run
        // Allocated = 12.00, Used = 5.00. Remaining balance = 7.00.
        // Policy carry forward limit is 5.00.
        // Carried amount = min(7.00, 5.00) = 5.00. Expired = 2.00.
        $balance = LeaveBalance::where('employee_id', $this->employee->id)->where('leave_type_id', $this->leaveType->id)->first();
        $balance->update([
            'allocated_balance' => 12.00,
            'used_balance' => 5.00,
        ]);

        $this->artisan('leave:carry-forward', ['year' => 2026])
             ->assertSuccessful();

        $balance->refresh();
        // Next year's balance values:
        // opening_balance = 5.00 (carried)
        // carry_forward_balance = 5.00 (carried)
        // allocated_balance = 12.00 (re-allocated annual)
        // used_balance = 0.00 (reset)
        $this->assertEquals(5.00, $balance->opening_balance);
        $this->assertEquals(5.00, $balance->carry_forward_balance);
        $this->assertEquals(12.00, $balance->allocated_balance);
        $this->assertEquals(0.00, $balance->used_balance);
        $this->assertEquals(17.00, $balance->remaining_balance);

        // Check carry forward transaction audit log
        $this->assertDatabaseHas('leave_carry_forwards', [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'amount_carried' => 5.00,
            'amount_expired' => 2.00,
            'run_year' => 2026,
        ]);
    }
}
