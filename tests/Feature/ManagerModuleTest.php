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
use App\Models\AttendanceCorrection;
use App\Models\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ManagerModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $otherManager;
    protected $directReport;
    protected $indirectReport;
    protected $otherEmployee;
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

        // Setup base organization entities
        $this->location = Location::create([
            'location_name' => 'HQ Noida',
            'location_code' => 'HQ-ND',
            'status' => 'active',
        ]);

        $this->shift = Shift::create([
            'shift_name' => 'Standard',
            'shift_code' => 'STD',
            'start_time' => '09:00',
            'end_time' => '18:00',
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

        $this->otherManager = User::create([
            'name' => 'Other Manager',
            'email' => 'other.manager@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->otherManager->assignRole('Manager');

        $this->directReport = User::create([
            'name' => 'Direct Report',
            'email' => 'direct@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->directReport->assignRole('Employee');

        $this->indirectReport = User::create([
            'name' => 'Indirect Report',
            'email' => 'indirect@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->indirectReport->assignRole('Employee');

        $this->otherEmployee = User::create([
            'name' => 'Other Employee',
            'email' => 'other.emp@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->otherEmployee->assignRole('Employee');

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
                'user_id' => $this->otherManager->id,
                'employee_code' => 'EMP-003',
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
                'user_id' => $this->directReport->id,
                'employee_code' => 'EMP-004',
                'joining_date' => '2026-01-01',
                'manager_id' => $this->manager->id,
                'location_id' => $this->location->id,
                'department_id' => $this->department->id,
                'designation_id' => $this->designation->id,
                'shift_id' => $this->shift->id,
                'gender' => 'male',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->indirectReport->id,
                'employee_code' => 'EMP-005',
                'joining_date' => '2026-01-01',
                'manager_id' => $this->directReport->id, // indirect report to manager via directReport
                'location_id' => $this->location->id,
                'department_id' => $this->department->id,
                'designation_id' => $this->designation->id,
                'shift_id' => $this->shift->id,
                'gender' => 'female',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->otherEmployee->id,
                'employee_code' => 'EMP-006',
                'joining_date' => '2026-01-01',
                'manager_id' => $this->otherManager->id,
                'location_id' => $this->location->id,
                'department_id' => $this->department->id,
                'designation_id' => $this->designation->id,
                'shift_id' => $this->shift->id,
                'gender' => 'female',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /** @test */
    public function manager_dashboard_displays_correct_dynamic_metrics()
    {
        $today = '2026-06-05';
        \Carbon\Carbon::setTestNow($today);

        // 1. Setup Attendance
        // Direct Report is Present
        $att = Attendance::create([
            'user_id' => $this->directReport->id,
            'attendance_date' => $today,
            'attendance_status' => 'Present',
            'shift_id' => $this->shift->id,
            'clock_in' => $today . ' 09:00:00',
        ]);
        
        // Indirect Report is On Leave
        Attendance::create([
            'user_id' => $this->indirectReport->id,
            'attendance_date' => $today,
            'attendance_status' => 'On Leave',
            'shift_id' => $this->shift->id,
        ]);

        // Other Employee is Present (but not reporting to this manager)
        Attendance::create([
            'user_id' => $this->otherEmployee->id,
            'attendance_date' => $today,
            'attendance_status' => 'Present',
            'shift_id' => $this->shift->id,
            'clock_in' => $today . ' 09:00:00',
        ]);



        // 2. Setup Leave Request
        LeaveRequest::create([
            'employee_id' => $this->directReport->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => $today,
            'end_date' => $today,
            'total_days' => 1,
            'status' => 'pending',
            'reason' => 'Doctor visit',
            'emergency_phone' => '1234567890',
        ]);

        // 3. Setup Correction Request
        AttendanceCorrection::create([
            'user_id' => $this->directReport->id,
            'attendance_id' => $att->id,
            'requested_date' => $today,
            'requested_clock_in' => $today . ' 09:00:00',
            'requested_clock_out' => $today . ' 18:00:00',
            'status' => 'pending',
            'reason' => 'Forgot to punch',
        ]);

        $response = $this->actingAs($this->manager)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('teamSize', 1); // directReports = directReport only
        $response->assertViewHas('teamPresent', 1); // directReport is present
        $response->assertViewHas('teamOnLeave', 0); // indirectReport is on leave, but directReports = 0 on leave
        $response->assertViewHas('pendingApprovals', 2); // 1 leave + 1 correction
        
        \Carbon\Carbon::setTestNow();
    }

    /** @test */
    public function employees_cannot_access_manager_routes()
    {
        $this->actingAs($this->otherEmployee)->get('/team-members')->assertStatus(403);
        $this->actingAs($this->otherEmployee)->get('/team-members/structure')->assertStatus(403);
        $this->actingAs($this->otherEmployee)->get('/team-members/' . $this->directReport->id)->assertStatus(403);
        $this->actingAs($this->otherEmployee)->get('/team-calendar')->assertStatus(403);
        $this->actingAs($this->otherEmployee)->get('/team-reports')->assertStatus(403);
    }

    /** @test */
    public function managers_can_access_their_team_management_views()
    {
        // 1. Team Members directory list
        $responseList = $this->actingAs($this->manager)->get('/team-members');
        $responseList->assertStatus(200);
        $responseList->assertSee($this->directReport->name);
        $responseList->assertDontSee($this->otherEmployee->name);

        // 2. Direct Report profile detail view
        $responseProfile = $this->actingAs($this->manager)->get('/team-members/' . $this->directReport->id);
        $responseProfile->assertStatus(200);
        $responseProfile->assertSee($this->directReport->name);

        // 3. Blocked from viewing non-reporting employee profile
        $this->actingAs($this->manager)->get('/team-members/' . $this->otherEmployee->id)->assertStatus(403);

        // 4. Reporting Structure view
        $responseStructure = $this->actingAs($this->manager)->get('/team-members/structure');
        $responseStructure->assertStatus(200);
        $responseStructure->assertSee($this->manager->name);
        $responseStructure->assertSee($this->directReport->name);

        // 5. Team Calendar view
        $responseCalendar = $this->actingAs($this->manager)->get('/team-calendar');
        $responseCalendar->assertStatus(200);

        // 6. Team Reports view
        $responseReports = $this->actingAs($this->manager)->get('/team-reports');
        $responseReports->assertStatus(200);
    }

    /** @test */
    public function managers_cannot_access_admin_only_routes()
    {
        $this->actingAs($this->manager)->get('/roles')->assertStatus(403);
        $this->actingAs($this->manager)->get('/permissions')->assertStatus(403);
        $this->actingAs($this->manager)->get('/settings')->assertStatus(403);
    }

    /** @test */
    public function attendance_index_is_scoped_to_direct_reports_for_managers()
    {
        $today = '2026-06-05';

        // Direct Report attendance
        Attendance::create([
            'user_id' => $this->directReport->id,
            'attendance_date' => $today,
            'attendance_status' => 'Present',
            'shift_id' => $this->shift->id,
            'clock_in' => '09:00:00',
        ]);

        // Other Employee attendance
        Attendance::create([
            'user_id' => $this->otherEmployee->id,
            'attendance_date' => $today,
            'attendance_status' => 'Present',
            'shift_id' => $this->shift->id,
            'clock_in' => '09:00:00',
        ]);

        // Manager view
        $response = $this->actingAs($this->manager)->get('/attendance?date=' . $today);
        $response->assertStatus(200);
        $response->assertSee($this->directReport->name);
        $response->assertDontSee($this->otherEmployee->name);

        // Admin view sees both
        $adminResponse = $this->actingAs($this->admin)->get('/attendance?date=' . $today);
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee($this->directReport->name);
        $adminResponse->assertSee($this->otherEmployee->name);
    }
}
