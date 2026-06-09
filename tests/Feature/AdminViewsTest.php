<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminViewsTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\GovernanceAndSettingsSeeder::class);
        $this->seed(\Database\Seeders\ReportsAndAnalyticsSeeder::class);

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

    public function test_admin_can_access_settings_page()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/settings');

        $response->assertStatus(200);
        $response->assertViewIs('settings.index');
        $response->assertSee('System Configurations');
    }

    public function test_employee_cannot_access_settings_page()
    {
        $response = $this->actingAs($this->employeeUser)
            ->get('/settings');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_reports_page()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/reports');

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertSee('Central Reporting Portal');
    }

    public function test_employee_cannot_access_reports_page()
    {
        $response = $this->actingAs($this->employeeUser)
            ->get('/reports');

        $response->assertStatus(403);
    }
}
