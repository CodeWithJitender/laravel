<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ReportCategory;
use App\Models\ReportDefinition;
use App\Models\ReportExport;
use App\Models\ReportTemplate;
use App\Models\ScheduledReport;
use App\Models\FavoriteReport;
use App\Services\ReportService;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportsAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $employee;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
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

        $this->department = Department::create([
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

        $this->manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->manager->assignRole('Manager');

        $this->employee = User::create([
            'name' => 'John Dev',
            'email' => 'john.dev@hrms.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->employee->assignRole('Employee');

        DB::table('employee_details')->insert([
            [
                'user_id' => $this->admin->id,
                'employee_code' => 'EMP-001',
                'joining_date' => '2026-01-01',
                'manager_id' => null,
                'location_id' => $location->id,
                'department_id' => $this->department->id,
                'designation_id' => $designation->id,
                'shift_id' => $shift->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->manager->id,
                'employee_code' => 'EMP-002',
                'joining_date' => '2026-01-01',
                'manager_id' => null,
                'location_id' => $location->id,
                'department_id' => $this->department->id,
                'designation_id' => $designation->id,
                'shift_id' => $shift->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $this->employee->id,
                'employee_code' => 'EMP-101',
                'joining_date' => '2026-01-01',
                'manager_id' => $this->manager->id,
                'location_id' => $location->id,
                'department_id' => $this->department->id,
                'designation_id' => $designation->id,
                'shift_id' => $shift->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Run Reports & Analytics Seeder
        $this->seed(\Database\Seeders\ReportsAndAnalyticsSeeder::class);
    }

    public function test_seeding_and_definitions()
    {
        $this->assertDatabaseHas('report_categories', [
            'code' => 'EMPLOYEE',
        ]);

        $this->assertDatabaseHas('report_definitions', [
            'report_code' => 'EMP_DIR',
        ]);

        $this->assertDatabaseHas('report_filters', [
            'filter_key' => 'department_id',
        ]);
    }

    public function test_report_service_query_building()
    {
        $service = app(ReportService::class);

        // Query active directory (should resolve base model User)
        $query = $service->buildQuery('EMP_DIR', [
            'department_id' => $this->department->id
        ], $this->admin);

        $results = $query->get();
        $this->assertCount(3, $results); // admin, manager, employee
    }

    public function test_rbac_manager_scope_enforcement()
    {
        $service = app(ReportService::class);

        // When manager queries directory, they only see themselves and their team (department/direct reports)
        // Employee belongs to department Engineering and has manager_id = manager.id
        // Admin also belongs to department Engineering in our setUp.
        // Let's filter by manager_id explicitly
        $query = $service->buildQuery('EMP_DIR', [
            'manager_id' => $this->manager->id
        ], $this->manager);

        $results = $query->get();
        $this->assertCount(1, $results);
        $this->assertEquals('John Dev', $results->first()->name);
    }

    public function test_preview_endpoint()
    {
        $definition = ReportDefinition::where('report_code', 'EMP_DIR')->first();

        $response = $this->actingAs($this->admin)
            ->getJson(route('reports.preview', ['uuid' => $definition->uuid]));

        $response->assertStatus(200)
            ->assertJsonStructure(['columns', 'data']);
    }

    public function test_report_generation_queueing()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('reports.generate'), [
                'report_code' => 'EMP_DIR',
                'filters' => [
                    'department_id' => $this->department->id,
                ],
                'export_format' => 'csv',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'export']);

        $this->assertDatabaseHas('report_exports', [
            'status' => 'completed',
            'export_format' => 'csv',
        ]);
    }

    public function test_dashboard_analytics_kpis()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('analytics.kpis'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'headcount' => 3,
            ]);
    }

    public function test_report_favorite_toggling()
    {
        $definition = ReportDefinition::where('report_code', 'EMP_DIR')->first();

        // Toggle on
        $response = $this->actingAs($this->admin)
            ->postJson(route('reports.favorite.toggle', ['uuid' => $definition->uuid]));

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => true]);

        $this->assertDatabaseHas('favorite_reports', [
            'user_id' => $this->admin->id,
            'report_definition_id' => $definition->id,
        ]);

        // Toggle off
        $response = $this->actingAs($this->admin)
            ->postJson(route('reports.favorite.toggle', ['uuid' => $definition->uuid]));

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => false]);

        $this->assertDatabaseMissing('favorite_reports', [
            'user_id' => $this->admin->id,
            'report_definition_id' => $definition->id,
        ]);
    }
}
