<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $employeeUser;
    protected $location;
    protected $department;
    protected $designation;
    protected $shift;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        // Create setup data
        $this->location = Location::create([
            'location_name' => 'Mumbai HQ',
            'location_code' => 'MUM-01',
            'status' => 'active',
            'timezone' => 'Asia/Kolkata',
        ]);

        $this->shift = Shift::create([
            'shift_name' => 'Morning',
            'shift_code' => 'MORN',
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
            'level' => 3,
            'status' => 'active',
        ]);

        // Create Admin
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->adminUser->assignRole('Admin');

        // Create Employee
        $this->employeeUser = User::create([
            'name' => 'Employee User',
            'email' => 'employee@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->employeeUser->assignRole('Employee');
    }

    public function test_admin_can_view_employees_list()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/employees');

        $response->assertStatus(200);
        $response->assertViewIs('employees.index');
        $response->assertSee($this->adminUser->name);
        $response->assertSee($this->employeeUser->name);
    }

    public function test_employee_cannot_view_employees_list()
    {
        $response = $this->actingAs($this->employeeUser)
            ->get('/employees');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_create_form()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/employees/create');

        $response->assertStatus(200);
        $response->assertViewIs('employees.create');
    }

    public function test_admin_can_store_new_employee()
    {
        $employeeData = [
            'name' => 'John Doe',
            'email' => 'johndoe@company.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
            'role' => 'Employee',
            'employee_code' => 'EMP-007',
            'joining_date' => '2026-06-01',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'gender' => 'male',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/employees', $employeeData);

        $response->assertRedirect('/employees');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'johndoe@company.com',
            'status' => 'active',
        ]);

        $user = User::where('email', 'johndoe@company.com')->first();
        $this->assertTrue($user->hasRole('Employee'));

        $this->assertDatabaseHas('employee_details', [
            'user_id' => $user->id,
            'employee_code' => 'EMP-007',
            'gender' => 'male',
        ]);
    }

    public function test_store_validation_fails_on_duplicate_email_or_code()
    {
        // First create detail for employeeUser
        DB::table('employee_details')->insert([
            'user_id' => $this->employeeUser->id,
            'employee_code' => 'EMP-001',
            'joining_date' => '2026-01-01',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'gender' => 'female',
        ]);

        // Duplicate email
        $invalidData1 = [
            'name' => 'Jane Smith',
            'email' => 'employee@company.com', // Duplicate
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
            'role' => 'Employee',
            'employee_code' => 'EMP-002',
            'joining_date' => '2026-06-01',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'gender' => 'female',
        ];

        $response1 = $this->actingAs($this->adminUser)
            ->post('/employees', $invalidData1);

        $response1->assertSessionHasErrors('email');

        // Duplicate code
        $invalidData2 = [
            'name' => 'Jane Smith',
            'email' => 'janesmith@company.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
            'role' => 'Employee',
            'employee_code' => 'EMP-001', // Duplicate
            'joining_date' => '2026-06-01',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'gender' => 'female',
        ];

        $response2 = $this->actingAs($this->adminUser)
            ->post('/employees', $invalidData2);

        $response2->assertSessionHasErrors('employee_code');
    }

    public function test_admin_can_view_employee_details()
    {
        // First create detail
        DB::table('employee_details')->insert([
            'user_id' => $this->employeeUser->id,
            'employee_code' => 'EMP-001',
            'joining_date' => '2026-01-01',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'gender' => 'female',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get("/employees/{$this->employeeUser->id}");

        $response->assertStatus(200);
        $response->assertViewIs('employees.show');
        $response->assertSee($this->employeeUser->name);
    }

    public function test_admin_can_update_employee()
    {
        // First create detail
        DB::table('employee_details')->insert([
            'user_id' => $this->employeeUser->id,
            'employee_code' => 'EMP-001',
            'joining_date' => '2026-01-01',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'gender' => 'female',
        ]);

        $updateData = [
            'name' => 'Employee Updated',
            'email' => 'employee.updated@company.com',
            'status' => 'active',
            'role' => 'Manager',
            'employee_code' => 'EMP-001-NEW',
            'joining_date' => '2026-01-01',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'gender' => 'female',
        ];

        $response = $this->actingAs($this->adminUser)
            ->put("/employees/{$this->employeeUser->id}", $updateData);

        $response->assertRedirect('/employees');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->employeeUser->id,
            'name' => 'Employee Updated',
            'email' => 'employee.updated@company.com',
        ]);

        $user = User::find($this->employeeUser->id);
        $this->assertTrue($user->hasRole('Manager'));

        $this->assertDatabaseHas('employee_details', [
            'user_id' => $user->id,
            'employee_code' => 'EMP-001-NEW',
        ]);
    }

    public function test_admin_can_delete_employee()
    {
        $response = $this->actingAs($this->adminUser)
            ->delete("/employees/{$this->employeeUser->id}");

        $response->assertRedirect('/employees');
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('users', [
            'id' => $this->employeeUser->id,
        ]);
    }

    public function test_admin_cannot_delete_self()
    {
        $response = $this->actingAs($this->adminUser)
            ->delete("/employees/{$this->adminUser->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
            'deleted_at' => null,
        ]);
    }
}
