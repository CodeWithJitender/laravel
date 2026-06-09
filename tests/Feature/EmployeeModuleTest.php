<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Holiday;
use App\Models\Payslip;
use App\Models\LeaveBalance;
use App\Models\EmergencyContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

class EmployeeModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $employee;
    protected $location;
    protected $shift;
    protected $department;
    protected $designation;
    protected $leaveType;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\HolidayTypeSeeder::class);

        // Setup base organization entities
        $this->location = Location::create([
            'location_name' => 'HQ Noida',
            'location_code' => 'HQ-ND',
            'status' => 'active',
        ]);

        $this->shift = Shift::create([
            'shift_name' => 'Standard',
            'shift_code' => 'STD',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'grace_period_minutes' => 15,
            'break_minutes' => 60,
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

        $this->leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'color' => '#6366f1',
            'is_paid' => true,
            'status' => 'active',
        ]);

        // Create Users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->admin->assignRole('Admin');

        $this->manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->manager->assignRole('Manager');

        $this->employee = User::create([
            'name' => 'Employee User',
            'email' => 'employee@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->employee->assignRole('Employee');

        // Create Employee Details
        DB::table('employee_details')->insert([
            [
                'user_id' => $this->admin->id,
                'employee_code' => 'EMP-001',
                'joining_date' => '2026-01-01',
                'manager_id' => null,
                'location_id' => $this->location->id,
                'department_id' => $this->department->id,
                'designation_id' => $this->designation->id,
                'shift_id' => $this->shift->id,
                'gender' => 'male',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->manager->id,
                'employee_code' => 'EMP-002',
                'joining_date' => '2026-01-01',
                'manager_id' => null,
                'location_id' => $this->location->id,
                'department_id' => $this->department->id,
                'designation_id' => $this->designation->id,
                'shift_id' => $this->shift->id,
                'gender' => 'male',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->employee->id,
                'employee_code' => 'EMP-003',
                'joining_date' => '2026-01-01',
                'manager_id' => $this->manager->id,
                'location_id' => $this->location->id,
                'department_id' => $this->department->id,
                'designation_id' => $this->designation->id,
                'shift_id' => $this->shift->id,
                'gender' => 'male',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /** @test */
    public function employee_can_access_dashboard_and_see_dynamic_metrics()
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // 1. Setup Attendance
        Attendance::create([
            'user_id' => $this->employee->id,
            'attendance_date' => $today,
            'attendance_status' => 'Present',
            'shift_id' => $this->shift->id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 17:00:00',
            'worked_hours' => 8.0,
        ]);

        // 2. Setup Leave Balance
        LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'allocated_balance' => 15.0,
            'used_balance' => 3.0,
            'remaining_balance' => 12.0,
        ]);

        // 3. Setup Leave Request
        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => $today,
            'end_date' => $today,
            'total_days' => 1,
            'status' => 'pending',
            'reason' => 'Sickness',
            'emergency_phone' => '1234567890',
        ]);

        $holidayType = \App\Models\HolidayType::where('code', 'national')->first();

        // 4. Setup Holiday
        $holiday = Holiday::create([
            'holiday_name' => 'Independence Day',
            'holiday_code' => 'IND-DAY',
            'holiday_date' => '2026-07-04',
            'holiday_type_id' => $holidayType->id,
            'status' => 'published',
            'is_paid' => true,
        ]);
        $holiday->locations()->attach($this->location->id);

        $response = $this->actingAs($this->employee)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('workingHoursSummary', 8.0);
        $response->assertViewHas('pendingLeaveRequestsCount', 1);
    }

    /** @test */
    public function employee_can_clock_in_and_out_via_widget()
    {
        $today = Carbon::today()->toDateString();

        // Perform Clock In
        $responseIn = $this->actingAs($this->employee)->post('/attendance/clock-in', [
            'method' => 'web',
            'remarks' => 'Punching from dashboard',
        ]);

        $responseIn->assertRedirect();
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->employee->id,
            'clock_out' => null,
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $this->employee->id,
            'type' => 'clock_in',
            'method' => 'web',
        ]);

        // Perform Clock Out
        $responseOut = $this->actingAs($this->employee)->post('/attendance/clock-out', [
            'method' => 'web',
            'remarks' => 'Leaving for today',
        ]);

        $responseOut->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->employee->id,
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $this->employee->id,
            'type' => 'clock_out',
            'method' => 'web',
        ]);

        $this->assertNotNull(
            Attendance::where('user_id', $this->employee->id)
                ->first()
                ->clock_out
        );
    }

    /** @test */
    public function employee_can_view_monthly_attendance_summary_and_list_history()
    {
        $today = Carbon::today()->toDateString();

        Attendance::create([
            'user_id' => $this->employee->id,
            'attendance_date' => $today,
            'attendance_status' => 'Present',
            'shift_id' => $this->shift->id,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 18:00:00',
            'worked_hours' => 9.0,
        ]);

        // Standard month view
        $responseCalendar = $this->actingAs($this->employee)->get('/attendance/my-history');
        $responseCalendar->assertStatus(200);

        // List list history view
        $responseList = $this->actingAs($this->employee)->get('/attendance/my-history?view=list');
        $responseList->assertStatus(200);
        $responseList->assertSee('9.0');
    }

    /** @test */
    public function employee_can_view_leaves_tabbed_hub()
    {
        $responseBalance = $this->actingAs($this->employee)->get('/leave?tab=balance');
        $responseBalance->assertStatus(200);

        $responseHistory = $this->actingAs($this->employee)->get('/leave?tab=history');
        $responseHistory->assertStatus(200);

        $responseStatus = $this->actingAs($this->employee)->get('/leave?tab=status');
        $responseStatus->assertStatus(200);
    }

    /** @test */
    public function employee_can_update_demographics_excluding_restricted_fields()
    {
        $response = $this->actingAs($this->employee)->put('/profile', [
            'phone' => '9876543210',
            'gender' => 'male',
            'dob' => '1995-05-15',
            'bank_name' => 'First National Bank',
            'bank_account_no' => '123456789',
            'pan_no' => 'ABCDE1234F',
            // Try to modify restricted fields
            'employee_code' => 'EMP-HACKED',
            'joining_date' => '2020-01-01',
        ]);

        $response->assertRedirect();
        
        $detail = $this->employee->fresh()->employeeDetail;

        $this->assertEquals('9876543210', $detail->phone);
        $this->assertEquals('male', $detail->gender);
        $this->assertEquals('1995-05-15', $detail->dob->toDateString());
        $this->assertEquals('First National Bank', $detail->bank_name);
        $this->assertEquals('123456789', $detail->bank_account_no);
        $this->assertEquals('ABCDE1234F', $detail->pan_no);

        // Ensure restricted fields are NOT modified
        $this->assertEquals('EMP-003', $detail->employee_code);
        $this->assertEquals('2026-01-01', $detail->joining_date->toDateString());
    }

    /** @test */
    public function employee_can_manage_emergency_contacts_crud()
    {
        $detail = $this->employee->fresh()->employeeDetail;

        // 1. Store contact
        $responseStore = $this->actingAs($this->employee)->post('/profile/emergency-contacts', [
            'name' => 'Jane Doe',
            'relationship' => 'Spouse',
            'phone' => '555-0199',
            'email' => 'jane@example.com',
            'is_primary' => 1,
        ]);

        $responseStore->assertRedirect();
        $this->assertDatabaseHas('emergency_contacts', [
            'employee_detail_id' => $detail->id,
            'name' => 'Jane Doe',
            'relationship' => 'Spouse',
            'phone' => '555-0199',
            'is_primary' => true,
        ]);

        $contact = EmergencyContact::where('employee_detail_id', $detail->id)->first();

        // 2. Update contact
        $responseUpdate = $this->actingAs($this->employee)->put('/profile/emergency-contacts/' . $contact->id, [
            'name' => 'Jane Smith',
            'relationship' => 'Wife',
            'phone' => '555-0200',
            'email' => 'janesmith@example.com',
            'is_primary' => 0,
        ]);

        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('emergency_contacts', [
            'id' => $contact->id,
            'name' => 'Jane Smith',
            'relationship' => 'Wife',
            'phone' => '555-0200',
            'is_primary' => false,
        ]);

        // 3. Destroy contact
        $responseDelete = $this->actingAs($this->employee)->delete('/profile/emergency-contacts/' . $contact->id);
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('emergency_contacts', [
            'id' => $contact->id,
        ]);
    }

    /** @test */
    public function employee_cannot_access_unauthorized_routes()
    {
        // Settings page
        $this->actingAs($this->employee)->get('/settings')->assertStatus(403);

        // Roles management
        $this->actingAs($this->employee)->get('/roles')->assertStatus(403);

        // Employee directory
        $this->actingAs($this->employee)->get('/employees')->assertStatus(403);

        // Departments configuration
        $this->actingAs($this->employee)->get('/departments')->assertStatus(403);
    }
}
