<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeavePolicy;
use App\Models\LeaveBalance;
use App\Models\HolidayType;
use App\Models\Holiday;

use App\Models\Attendance;
use App\Models\OfficeTiming;
use App\Services\HolidayService;

use App\Services\LeaveRequestService;
use App\Services\LeaveBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Tests\TestCase;

class HolidayManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $locationNoida;
    protected $locationDelhi;
    protected $shift;
    protected $department;
    protected $designation;
    protected $employeeNoida;
    protected $employeeDelhi;
    protected $manager;
    protected $holidayTypeCL;
    protected $holidayTypeNational;

    protected $leaveType;
    protected $leavePolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\HolidayTypeSeeder::class);

        $this->locationNoida = Location::create([
            'location_name' => 'Noida HQ',
            'location_code' => 'ND-01',
            'status' => 'active',
        ]);

        $this->locationDelhi = Location::create([
            'location_name' => 'Delhi Office',
            'location_code' => 'DL-01',
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
            'location_id' => $this->locationNoida->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'manager_id' => null,
            'gender' => 'female',
        ]);

        // Create Noida Employee
        $this->employeeNoida = User::create([
            'name' => 'John Noida',
            'email' => 'noida@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->employeeNoida->assignRole('Employee');

        DB::table('employee_details')->insert([
            'user_id' => $this->employeeNoida->id,
            'employee_code' => 'EMP-200',
            'joining_date' => '2026-01-02',
            'location_id' => $this->locationNoida->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'manager_id' => $this->manager->id,
            'gender' => 'male',
        ]);

        // Create Delhi Employee
        $this->employeeDelhi = User::create([
            'name' => 'John Delhi',
            'email' => 'delhi@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->employeeDelhi->assignRole('Employee');

        DB::table('employee_details')->insert([
            'user_id' => $this->employeeDelhi->id,
            'employee_code' => 'EMP-300',
            'joining_date' => '2026-01-02',
            'location_id' => $this->locationDelhi->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'manager_id' => $this->manager->id,
            'gender' => 'male',
        ]);

        // Refresh all users to populate relation caches
        $this->manager->refresh();
        $this->employeeNoida->refresh();
        $this->employeeDelhi->refresh();

        $this->holidayTypeNational = HolidayType::where('code', 'national')->first();


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
            'notice_period_days' => 0, // no notice period for testing ease
            'status' => 'active',
        ]);

        app(LeaveBalanceService::class)->initializeEmployeeBalances($this->employeeNoida);
        app(LeaveBalanceService::class)->initializeEmployeeBalances($this->employeeDelhi);

        // Office Timing Setup
        OfficeTiming::create([
            'name' => 'Default timing',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'weekly_off' => ['Saturday', 'Sunday'],
            'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'minimum_hours' => 8.00,
            'half_day_hours' => 4.00,
        ]);
    }

    public function test_location_based_holiday_visibility()
    {
        $holidayService = app(HolidayService::class);

        // Create Noida specific holiday
        $holidayNoida = $holidayService->createHoliday([
            'holiday_name' => 'Noida Day',
            'holiday_code' => 'ND-DAY',
            'holiday_date' => '2026-10-10',
            'holiday_type_id' => $this->holidayTypeNational->id,
            'status' => 'published',
            'location_ids' => [$this->locationNoida->id]
        ]);



        // Noida employee should see Noida Day as a holiday
        $this->assertTrue($holidayService->isHolidayForUserLocation($this->employeeNoida, '2026-10-10'));

        // Delhi employee should NOT see Noida Day as a holiday
        $this->assertFalse($holidayService->isHolidayForUserLocation($this->employeeDelhi, '2026-10-10'));
    }

    public function test_leave_duration_skips_holidays()
    {
        // 2026-06-08 is a Monday. Let's make it a holiday for Noida.
        $holidayService = app(HolidayService::class);
        $holidayService->createHoliday([
            'holiday_name' => 'Noida Fest',
            'holiday_code' => 'ND-FEST',
            'holiday_date' => '2026-06-08',
            'holiday_type_id' => $this->holidayTypeNational->id,
            'status' => 'published',
            'location_ids' => [$this->locationNoida->id]
        ]);

        // Submit a leave request from 2026-06-05 (Friday) to 2026-06-09 (Tuesday)
        // Spans: Friday (work), Sat (weekly off), Sun (weekly off), Mon (Holiday), Tue (work).
        // Friday and Tuesday are active working days = 2.0 days.
        // If holiday check was missing, it would have been 3.0 days (Fri, Mon, Tue).
        $leaveRequest = app(LeaveRequestService::class)->submitRequest($this->employeeNoida, [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-06-05',
            'end_date' => '2026-06-09',
            'reason' => 'Family event',
            'emergency_phone' => '1234557890',
        ]);

        $this->assertEquals(2.0, $leaveRequest->total_days);
    }

    public function test_daily_absent_marker_skips_holiday()
    {
        // Noida Fest on 2026-06-08 (Monday)
        $holidayService = app(HolidayService::class);
        $holidayService->createHoliday([
            'holiday_name' => 'Noida Fest',
            'holiday_code' => 'ND-FEST',
            'holiday_date' => '2026-06-08',
            'holiday_type_id' => $this->holidayTypeNational->id,
            'status' => 'published',
            'location_ids' => [$this->locationNoida->id]
        ]);

        // Run absent marking command for 2026-06-08
        Artisan::call('attendance:mark-absents', [
            'date' => '2026-06-08'
        ]);

        // Noida employee should have a Holiday record
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->employeeNoida->id,
            'attendance_status' => 'Holiday',
        ]);
        $attNoida = Attendance::where('user_id', $this->employeeNoida->id)->first();
        $this->assertEquals('2026-06-08', Carbon::parse($attNoida->attendance_date)->toDateString());

        // Delhi employee (no holiday) should have an Absent record
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->employeeDelhi->id,
            'attendance_status' => 'Absent',
        ]);
        $attDelhi = Attendance::where('user_id', $this->employeeDelhi->id)->first();
        $this->assertEquals('2026-06-08', Carbon::parse($attDelhi->attendance_date)->toDateString());
    }


}
