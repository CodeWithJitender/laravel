<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Department;
use App\Models\AuditLog;
use App\Models\ActivityLog;
use App\Models\FeatureFlag;
use App\Models\UserLoginHistory;
use App\Models\SystemSetting;
use App\Support\Facades\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class GovernanceAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        // Create Admin & Employee
        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->admin->assignRole('Admin');

        $this->employee = User::create([
            'name' => 'John Employee',
            'email' => 'employee@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->employee->assignRole('Employee');

        // Run Settings & Governance Seeder
        $this->seed(\Database\Seeders\GovernanceAndSettingsSeeder::class);
    }

    public function test_seeding_and_default_configs()
    {
        $this->assertDatabaseHas('company_settings', [
            'company_code' => 'HEC',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'app_name' => 'HRMS',
        ]);

        $this->assertDatabaseHas('feature_flags', [
            'flag_key' => 'payroll_module_enabled',
            'flag_value' => true,
        ]);
    }

    public function test_settings_engine_caching_and_accessors()
    {
        // Get value via Facade
        $appName = Settings::get('system.app_name');
        $this->assertEquals('HRMS', $appName);

        // Update settings and ensure cache flushes
        $this->actingAs($this->admin)
            ->putJson(route('settings.update', ['group' => 'system']), [
                'app_name' => 'New HR Portal',
                'app_version' => '1.1.0',
                'default_timezone' => 'UTC',
                'default_currency' => 'USD',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i:s',
                'language' => 'en',
                'system_status' => 'online',
            ])
            ->assertStatus(200);

        $this->assertEquals('New HR Portal', Settings::get('system.app_name'));
        $this->assertDatabaseHas('system_settings', [
            'app_name' => 'New HR Portal',
        ]);
    }

    public function test_audit_logs_observer_logs_actions()
    {
        // Acting as admin, create a department (which registers observed triggers)
        $this->actingAs($this->admin);

        $department = Department::create([
            'department_name' => 'Finance Division',
            'department_code' => 'FIN',
            'status' => 'active',
        ]);

        // Check if an audit log was created for 'create' action on Department
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'create',
            'record_type' => Department::class,
            'record_id' => $department->id,
        ]);

        // Update the department
        $department->update(['department_name' => 'Finance & Strategy']);

        // Check if audit log was created for 'update' action with correct old/new value snapshots
        $audit = AuditLog::where('action', 'update')
            ->where('record_type', Department::class)
            ->where('record_id', $department->id)
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals('Finance Division', $audit->old_values['department_name']);
        $this->assertEquals('Finance & Strategy', $audit->new_values['department_name']);
    }

    public function test_auth_event_listener_logs_activity()
    {
        // Simulate Login event trigger
        event(new \Illuminate\Auth\Events\Login('web', $this->employee, false));

        // User login history and activity log must exist
        $this->assertDatabaseHas('user_login_history', [
            'user_id' => $this->employee->id,
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->employee->id,
            'activity' => 'Login',
        ]);

        // Simulate failed login trigger
        event(new \Illuminate\Auth\Events\Failed('web', $this->employee, ['email' => 'employee@company.com']));

        $this->assertDatabaseHas('user_login_history', [
            'user_id' => $this->employee->id,
            'status' => 'failed',
        ]);
    }

    public function test_maintenance_mode_middleware()
    {
        // Set system to maintenance
        Settings::update('system', [
            'app_name' => 'HRMS',
            'app_version' => '1.0.0',
            'default_timezone' => 'UTC',
            'default_currency' => 'USD',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'language' => 'en',
            'system_status' => 'maintenance',
        ]);

        // Employee should be blocked (503)
        $this->actingAs($this->employee)
            ->getJson('/dashboard')
            ->assertStatus(503);

        // Admin should bypass and access successfully (200)
        $this->actingAs($this->admin)
            ->getJson('/dashboard')
            ->assertStatus(200);
    }

    public function test_session_timeout_middleware()
    {
        // 1. Logged in recently
        session(['last_activity_timestamp' => time()]);

        $this->actingAs($this->employee)
            ->getJson('/dashboard')
            ->assertStatus(200);

        // 2. Logged in past the timeout (e.g. 180 minutes ago, while timeout is 120)
        session(['last_activity_timestamp' => time() - (180 * 60)]);

        // Requesting should trigger timeout logout
        $response = $this->actingAs($this->employee)
            ->getJson('/dashboard');

        $response->assertStatus(401); // Unauthorized JSON timeout return
        $this->assertFalse(Auth::check()); // Logged out
    }
}
