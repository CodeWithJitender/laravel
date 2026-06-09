<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use App\Models\OfficeTiming;
use App\Models\DepartmentHead;
use App\Models\OrganizationalHierarchy;
use App\Services\DepartmentService;
use App\Services\DesignationService;
use App\Services\LocationService;
use App\Services\ShiftService;
use App\Services\OfficeTimingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrganizationSetupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles and permissions first
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    }

    public function test_cannot_delete_department_with_employees()
    {
        $location = Location::create([
            'location_name' => 'Noida HQ',
            'location_code' => 'ND-01',
            'status' => 'active',
        ]);

        $shift = Shift::create([
            'shift_name' => 'General',
            'shift_code' => 'GEN',
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

        $user = User::create([
            'name' => 'John Developer',
            'email' => 'john.dev@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        DB::table('employee_details')->insert([
            'user_id' => $user->id,
            'employee_code' => 'EMP-123',
            'joining_date' => '2026-01-01',
            'location_id' => $location->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'shift_id' => $shift->id,
        ]);

        $departmentService = app(DepartmentService::class);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot delete department because employees are currently assigned to it.");
        
        $departmentService->deleteDepartment($department->id);
    }

    public function test_can_delete_empty_department()
    {
        $department = Department::create([
            'department_name' => 'Marketing',
            'department_code' => 'MKT',
            'status' => 'active',
        ]);

        $departmentService = app(DepartmentService::class);
        $result = $departmentService->deleteDepartment($department->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted($department);
    }

    public function test_cannot_delete_designation_with_employees()
    {
        $location = Location::create([
            'location_name' => 'Noida HQ',
            'location_code' => 'ND-01',
            'status' => 'active',
        ]);

        $shift = Shift::create([
            'shift_name' => 'General',
            'shift_code' => 'GEN',
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

        $user = User::create([
            'name' => 'John Developer',
            'email' => 'john.dev@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        DB::table('employee_details')->insert([
            'user_id' => $user->id,
            'employee_code' => 'EMP-123',
            'joining_date' => '2026-01-01',
            'location_id' => $location->id,
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'shift_id' => $shift->id,
        ]);

        $designationService = app(DesignationService::class);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot delete designation because employees are currently assigned to it");
        
        $designationService->deleteDesignation($designation->id);
    }

    public function test_designation_hierarchy_association()
    {
        $ceo = Designation::create([
            'designation_name' => 'Chief Executive Officer',
            'designation_code' => 'CEO',
            'level' => 1,
            'status' => 'active',
        ]);
        OrganizationalHierarchy::create([
            'designation_id' => $ceo->id,
            'parent_designation_id' => null
        ]);

        $designationService = app(DesignationService::class);
        
        $director = $designationService->createDesignation([
            'designation_name' => 'Operations Director',
            'designation_code' => 'DIR',
            'level' => 2,
            'status' => 'active',
            'parent_designation_id' => $ceo->id
        ]);

        $this->assertNotNull($director->hierarchy);
        $this->assertEquals($ceo->id, $director->hierarchy->parent_designation_id);
        $this->assertEquals('Chief Executive Officer', $director->hierarchy->parentDesignation->designation_name);
    }

    public function test_default_office_timings_initialization()
    {
        $officeTimingService = app(OfficeTimingService::class);
        $timing = $officeTimingService->getDefault();

        $this->assertNotNull($timing);
        $this->assertEquals('Default Office Timing', $timing->name);
        $this->assertIsArray($timing->working_days);
        $this->assertContains('Monday', $timing->working_days);
    }
}
