<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $location;
    protected $shift;
    protected $department;
    protected $designation;
    protected $employee;
    protected $manager;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        // Create base organizational records
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
        ]);
    }

    public function test_employee_can_clock_in_and_out()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->employee);

        // 1. Clock In
        $clockInResponse = $this->post(route('attendance.clock_in'), [
            'remarks' => 'Starting work',
            'ip_address' => '127.0.0.1',
            'device_info' => 'PHPUnit Test',
            'method' => 'web'
        ]);

        $clockInResponse->assertRedirect();
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->employee->id,
            'remarks' => 'Starting work'
        ]);

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $this->employee->id,
            'type' => 'clock_in',
            'method' => 'web'
        ]);

        // 2. Clock Out
        $clockOutResponse = $this->post(route('attendance.clock_out'), [
            'ip_address' => '127.0.0.1',
            'device_info' => 'PHPUnit Test',
            'method' => 'web'
        ]);

        $clockOutResponse->assertRedirect();

        $attendance = Attendance::where('user_id', $this->employee->id)->first();

        $this->assertNotNull($attendance->clock_out);
        $this->assertNotNull($attendance->worked_hours);
        $this->assertNotEquals('Missed Punch', $attendance->attendance_status);

        $this->assertDatabaseHas('attendance_logs', [
            'attendance_id' => $attendance->id,
            'user_id' => $this->employee->id,
            'type' => 'clock_out'
        ]);
    }

    public function test_employee_can_submit_correction_request()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->employee);

        $response = $this->post(route('attendance.corrections.store'), [
            'requested_date' => now()->subDay()->toDateString(),
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'reason' => 'Forgot to clock in/out yesterday due to client meeting.',
        ]);

        $response->assertRedirect(route('attendance.corrections.index'));

        $correction = AttendanceCorrection::first();
        $this->assertNotNull($correction);
        $this->assertEquals($this->employee->id, $correction->user_id);
        $this->assertEquals(now()->subDay()->toDateString(), $correction->requested_date->toDateString());
        $this->assertEquals('pending', $correction->status);
        $this->assertEquals('Forgot to clock in/out yesterday due to client meeting.', $correction->reason);
    }

    public function test_manager_can_approve_correction_request()
    {
        $this->withoutExceptionHandling();
        // 1. Submit request as employee
        $this->actingAs($this->employee);
        $date = now()->subDay()->toDateString();
        
        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'attendance_date' => $date,
            'shift_id' => $this->shift->id,
            'attendance_status' => 'Absent'
        ]);

        $correction = AttendanceCorrection::create([
            'user_id' => $this->employee->id,
            'attendance_id' => $attendance->id,
            'requested_date' => $date,
            'requested_clock_in' => $date . ' 09:00:00',
            'requested_clock_out' => $date . ' 18:00:00',
            'reason' => 'Forgot to clock in/out',
            'status' => 'pending'
        ]);

        // 2. Approve request as Manager
        $this->actingAs($this->manager);

        $response = $this->put(route('attendance.corrections.review', $correction->id), [
            'status' => 'approved',
        ]);

        $response->assertRedirect(route('attendance.corrections.index'));

        $correction->refresh();
        $this->assertEquals('approved', $correction->status);
        $this->assertEquals($this->manager->id, $correction->approved_by);

        // Verify the underlying attendance was updated
        $attendance = $correction->attendance->fresh();
        $this->assertEquals('Present', $attendance->attendance_status);
        $this->assertEquals(8.00, $attendance->worked_hours); // 9 hours total, 1 hour break = 8 hours worked
        $this->assertEquals(0, $attendance->late_minutes);
    }

    public function test_unauthorized_user_cannot_approve_correction()
    {
        // 1. Create a request for employee
        $date = now()->subDay()->toDateString();
        
        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'attendance_date' => $date,
            'shift_id' => $this->shift->id,
            'attendance_status' => 'Absent'
        ]);

        $correction = AttendanceCorrection::create([
            'user_id' => $this->employee->id,
            'attendance_id' => $attendance->id,
            'requested_date' => $date,
            'requested_clock_in' => $date . ' 09:00:00',
            'requested_clock_out' => $date . ' 18:00:00',
            'reason' => 'Forgot to clock in/out',
            'status' => 'pending'
        ]);

        // 2. Try to approve as another employee
        $otherEmployee = User::create([
            'name' => 'Other Emp',
            'email' => 'other@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $otherEmployee->assignRole('Employee');

        $this->actingAs($otherEmployee);

        $response = $this->put(route('attendance.corrections.review', $correction->id), [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
        $correction->refresh();
        $this->assertEquals('pending', $correction->status);
    }
}
