<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use App\Models\Shift;
use App\Models\Department;
use App\Models\Designation;
use App\Models\OfficeTiming;
use App\Models\DepartmentHead;
use App\Models\OrganizationalHierarchy;
use App\Models\LeaveType;
use App\Models\LeavePolicy;
use App\Services\LeaveBalanceService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run Role & Permission Seeder
        $this->call(RoleAndPermissionSeeder::class);

        // 2. Create default location
        $location = Location::create([
            'location_name' => 'Headquarters',
            'location_code' => 'HQ',
            'address' => '123 Main St',
            'city' => 'New York',
            'state' => 'New York',
            'country' => 'USA',
            'postal_code' => '10001',
            'timezone' => 'America/New_York',
            'status' => 'active',
        ]);

        // 3. Create default shift
        $shift = Shift::create([
            'shift_name' => 'Regular Shift',
            'shift_code' => 'RS',
            'description' => 'Standard corporate working hours.',
            'start_time' => '09:00',
            'end_time' => '18:00',
            'grace_period_minutes' => 15,
            'break_minutes' => 60,
            'status' => 'active',
        ]);

        // 4. Create default office timing configuration
        $officeTiming = OfficeTiming::create([
            'name' => 'HQ Office Timing Policies',
            'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'start_time' => '09:00',
            'end_time' => '18:00',
            'minimum_hours' => 8.00,
            'half_day_hours' => 4.00,
            'weekly_off' => ['Saturday', 'Sunday'],
            'status' => 'active',
        ]);

        // 5. Create default department
        $department = Department::create([
            'department_name' => 'Engineering',
            'department_code' => 'ENG',
            'description' => 'Core software engineering and development department.',
            'status' => 'active',
        ]);

        // 6. Create designation levels hierarchy
        $ceo = Designation::create([
            'designation_name' => 'Chief Executive Officer',
            'designation_code' => 'CEO',
            'description' => 'Top executive officer of the organization.',
            'level' => 1,
            'status' => 'active',
        ]);
        OrganizationalHierarchy::create([
            'designation_id' => $ceo->id,
            'parent_designation_id' => null,
        ]);

        $director = Designation::create([
            'designation_name' => 'Director of Operations',
            'designation_code' => 'DIR',
            'description' => 'Operations director heading the divisions.',
            'level' => 2,
            'status' => 'active',
        ]);
        OrganizationalHierarchy::create([
            'designation_id' => $director->id,
            'parent_designation_id' => $ceo->id,
        ]);

        $manager = Designation::create([
            'designation_name' => 'Department Manager',
            'designation_code' => 'MGR',
            'description' => 'Manager level overseeing departments.',
            'level' => 3,
            'status' => 'active',
        ]);
        OrganizationalHierarchy::create([
            'designation_id' => $manager->id,
            'parent_designation_id' => $director->id,
        ]);

        $teamLead = Designation::create([
            'designation_name' => 'Team Lead',
            'designation_code' => 'TL',
            'description' => 'Team leads coordinating engineers.',
            'level' => 4,
            'status' => 'active',
        ]);
        OrganizationalHierarchy::create([
            'designation_id' => $teamLead->id,
            'parent_designation_id' => $manager->id,
        ]);

        $swe = Designation::create([
            'designation_name' => 'Software Engineer',
            'designation_code' => 'SWE',
            'description' => 'Software developer.',
            'level' => 5,
            'status' => 'active',
        ]);
        OrganizationalHierarchy::create([
            'designation_id' => $swe->id,
            'parent_designation_id' => $teamLead->id,
        ]);

        // 7. Create Admin User (assigning CEO designation)
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@company.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $admin->assignRole('Admin');

        DB::table('employee_details')->insert([
            'user_id' => $admin->id,
            'employee_code' => 'EMP-001',
            'joining_date' => '2025-01-01',
            'location_id' => $location->id,
            'department_id' => $department->id,
            'designation_id' => $ceo->id,
            'shift_id' => $shift->id,
            'gender' => 'male',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 8. Create Manager User (assigning Manager designation)
        $managerUser = User::create([
            'name' => 'Jane Manager',
            'email' => 'manager@company.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $managerUser->assignRole('Manager');

        DB::table('employee_details')->insert([
            'user_id' => $managerUser->id,
            'employee_code' => 'EMP-002',
            'joining_date' => '2025-01-01',
            'location_id' => $location->id,
            'department_id' => $department->id,
            'designation_id' => $manager->id,
            'shift_id' => $shift->id,
            'gender' => 'female',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Map Department Head for Engineering to Jane Manager
        DepartmentHead::create([
            'department_id' => $department->id,
            'user_id' => $managerUser->id,
        ]);

        // 9. Create Employee User (assigning Software Engineer designation)
        $employee = User::create([
            'name' => 'John Employee',
            'email' => 'employee@company.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $employee->assignRole('Employee');

        DB::table('employee_details')->insert([
            'user_id' => $employee->id,
            'employee_code' => 'EMP-003',
            'joining_date' => '2025-01-15',
            'manager_id' => $managerUser->id,
            'location_id' => $location->id,
            'department_id' => $department->id,
            'designation_id' => $swe->id,
            'shift_id' => $shift->id,
            'gender' => 'male',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 10. Create default leave types and policies
        $cl = LeaveType::create([
            'name' => 'Casual Leave',
            'code' => 'CL',
            'description' => 'Casual leave for personal matters.',
            'color' => '#6366f1',
            'is_paid' => true,
            'status' => 'active',
        ]);
        LeavePolicy::create([
            'leave_type_id' => $cl->id,
            'annual_allocation' => 12.00,
            'monthly_accrual' => false,
            'carry_forward_limit' => 5.00,
            'max_consecutive_days' => 5,
            'notice_period_days' => 2,
            'status' => 'active',
        ]);

        $sl = LeaveType::create([
            'name' => 'Sick Leave',
            'code' => 'SL',
            'description' => 'Sick leave for medical reasons.',
            'color' => '#ef4444',
            'is_paid' => true,
            'status' => 'active',
        ]);
        LeavePolicy::create([
            'leave_type_id' => $sl->id,
            'annual_allocation' => 12.00,
            'monthly_accrual' => false,
            'carry_forward_limit' => 0.00,
            'max_consecutive_days' => null,
            'notice_period_days' => 0,
            'status' => 'active',
        ]);

        $el = LeaveType::create([
            'name' => 'Earned Leave',
            'code' => 'EL',
            'description' => 'Earned leave accrued monthly for planned vacations.',
            'color' => '#10b981',
            'is_paid' => true,
            'status' => 'active',
        ]);
        LeavePolicy::create([
            'leave_type_id' => $el->id,
            'annual_allocation' => 18.00,
            'monthly_accrual' => true,
            'carry_forward_limit' => 10.00,
            'max_consecutive_days' => 10,
            'notice_period_days' => 15,
            'status' => 'active',
        ]);

        // 11. Initialize balances for all seeded users
        $balanceService = app(LeaveBalanceService::class);
        $users = User::all();
        foreach ($users as $u) {
            if ($u->employeeDetail) {
                $balanceService->initializeEmployeeBalances($u);
            }
        }

        // 12. Run Notification Seeder
        $this->call(NotificationSeeder::class);

        // 13. Run Holiday Type Seeder
        $this->call(HolidayTypeSeeder::class);

        // 14. Run Payroll Seeder
        $this->call(PayrollSeeder::class);

        // 15. Run Reports & Analytics Seeder
        $this->call(ReportsAndAnalyticsSeeder::class);

        // 16. Run Governance & Settings Seeder
        $this->call(GovernanceAndSettingsSeeder::class);
    }
}
