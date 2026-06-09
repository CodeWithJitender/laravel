<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportCategory;
use App\Models\ReportDefinition;
use App\Models\ReportFilter;
use Illuminate\Support\Str;

class ReportsAndAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Employee Reports Category
        $catEmployee = ReportCategory::create([
            'name' => 'Employee Reports',
            'code' => 'EMPLOYEE',
            'description' => 'Directory, joining, exits, and status reports.',
        ]);

        $empDir = ReportDefinition::create([
            'report_name' => 'Employee Directory',
            'report_code' => 'EMP_DIR',
            'category_id' => $catEmployee->id,
            'description' => 'A complete list of active and inactive employees.',
            'query_builder_config' => [
                'base_model' => 'App\Models\User',
                'with' => ['employeeDetail.department', 'employeeDetail.designation', 'employeeDetail.location']
            ],
            'default_columns' => [
                'name' => 'Name',
                'email' => 'Email Address',
                'employeeDetail.employee_code' => 'Employee Code',
                'employeeDetail.department.department_name' => 'Department',
                'employeeDetail.designation.designation_name' => 'Designation',
                'employeeDetail.location.location_name' => 'Location',
                'status' => 'Status',
            ],
        ]);

        ReportFilter::create([
            'report_definition_id' => $empDir->id,
            'filter_key' => 'department_id',
            'filter_label' => 'Department',
            'field_type' => 'select',
            'validation_rules' => 'nullable|exists:departments,id',
        ]);
        ReportFilter::create([
            'report_definition_id' => $empDir->id,
            'filter_key' => 'location_id',
            'filter_label' => 'Location',
            'field_type' => 'select',
            'validation_rules' => 'nullable|exists:locations,id',
        ]);

        $empJoin = ReportDefinition::create([
            'report_name' => 'Employee Joining Report',
            'report_code' => 'EMP_JOIN',
            'category_id' => $catEmployee->id,
            'description' => 'Report showing employees who joined during a specific date range.',
            'query_builder_config' => [
                'base_model' => 'App\Models\User',
                'with' => ['employeeDetail.department', 'employeeDetail.designation']
            ],
            'default_columns' => [
                'name' => 'Name',
                'employeeDetail.employee_code' => 'Employee Code',
                'employeeDetail.joining_date' => 'Joining Date',
                'employeeDetail.department.department_name' => 'Department',
                'employeeDetail.designation.designation_name' => 'Designation',
            ],
        ]);

        ReportFilter::create([
            'report_definition_id' => $empJoin->id,
            'filter_key' => 'start_date',
            'filter_label' => 'Start Date',
            'field_type' => 'date',
            'validation_rules' => 'required|date',
        ]);
        ReportFilter::create([
            'report_definition_id' => $empJoin->id,
            'filter_key' => 'end_date',
            'filter_label' => 'End Date',
            'field_type' => 'date',
            'validation_rules' => 'required|date|after_or_equal:start_date',
        ]);


        // 2. Attendance Reports Category
        $catAttendance = ReportCategory::create([
            'name' => 'Attendance Reports',
            'code' => 'ATTENDANCE',
            'description' => 'Daily punch sheets, overtime logs, and missed punch audits.',
        ]);

        $attDaily = ReportDefinition::create([
            'report_name' => 'Daily Attendance Report',
            'report_code' => 'ATT_DAILY',
            'category_id' => $catAttendance->id,
            'description' => 'List of employee clock-in and clock-out logs for a given date range.',
            'query_builder_config' => [
                'base_model' => 'App\Models\Attendance',
                'with' => ['employee.employeeDetail.department', 'employee.employeeDetail.location']
            ],
            'default_columns' => [
                'employee.name' => 'Employee Name',
                'attendance_date' => 'Date',
                'clock_in' => 'Clock In',
                'clock_out' => 'Clock Out',
                'overtime_minutes' => 'Overtime (Min)',
                'attendance_status' => 'Status',
            ],
        ]);

        ReportFilter::create([
            'report_definition_id' => $attDaily->id,
            'filter_key' => 'start_date',
            'filter_label' => 'Start Date',
            'field_type' => 'date',
            'validation_rules' => 'required|date',
        ]);
        ReportFilter::create([
            'report_definition_id' => $attDaily->id,
            'filter_key' => 'end_date',
            'filter_label' => 'End Date',
            'field_type' => 'date',
            'validation_rules' => 'required|date',
        ]);


        // 3. Leave Reports Category
        $catLeave = ReportCategory::create([
            'name' => 'Leave Reports',
            'code' => 'LEAVE',
            'description' => 'Remaining balances, approved requests, and department calendar overview.',
        ]);

        $leaveRequests = ReportDefinition::create([
            'report_name' => 'Leave Requests Report',
            'report_code' => 'LEAVE_REQUESTS',
            'category_id' => $catLeave->id,
            'description' => 'List of leave requests with status breakdown.',
            'query_builder_config' => [
                'base_model' => 'App\Models\LeaveRequest',
                'with' => ['employee.employeeDetail.department', 'leaveType']
            ],
            'default_columns' => [
                'employee.name' => 'Employee Name',
                'leaveType.name' => 'Leave Type',
                'start_date' => 'Start Date',
                'end_date' => 'End Date',
                'total_days' => 'Total Days',
                'status' => 'Status',
            ],
        ]);

        ReportFilter::create([
            'report_definition_id' => $leaveRequests->id,
            'filter_key' => 'status',
            'filter_label' => 'Request Status',
            'field_type' => 'select',
            'validation_rules' => 'nullable|in:pending,approved,rejected,cancelled',
        ]);


        // 4. Payroll Reports Category
        $catPayroll = ReportCategory::create([
            'name' => 'Payroll Reports',
            'code' => 'PAYROLL',
            'description' => 'Net salary payments, allowances, and tax deduction sheets.',
        ]);

        $payrollRegister = ReportDefinition::create([
            'report_name' => 'Payroll Register',
            'report_code' => 'PAYROLL_REGISTER',
            'category_id' => $catPayroll->id,
            'description' => 'Detailed statement of employee earnings and deductions.',
            'query_builder_config' => [
                'base_model' => 'App\Models\Payslip',
                'with' => ['employee.employeeDetail.department', 'payrollRunEmployee']
            ],
            'default_columns' => [
                'employee.name' => 'Employee Name',
                'reference_no' => 'Reference No',
                'total_earnings' => 'Gross Earnings',
                'total_deductions' => 'Total Deductions',
                'net_salary' => 'Net Salary Paid',
                'status' => 'Status',
            ],
        ]);

        ReportFilter::create([
            'report_definition_id' => $payrollRegister->id,
            'filter_key' => 'status',
            'filter_label' => 'Status',
            'field_type' => 'select',
            'validation_rules' => 'nullable|in:draft,published',
        ]);

        // Add default permissions to roles/permissions system
        $this->seedPermissions();
    }

    protected function seedPermissions(): void
    {
        $permissions = [
            'report.view',
            'report.generate',
            'report.export',
            'report.schedule',
            'report.template.manage',
            'analytics.view',
            'executive_report.view',
        ];

        foreach ($permissions as $permName) {
            \Spatie\Permission\Models\Permission::findOrCreate($permName, 'web');
        }

        // Assign all to Admin role
        $adminRole = \Spatie\Permission\Models\Role::findOrCreate('Admin', 'web');
        $adminRole->givePermissionTo($permissions);

        // Assign subset to Manager role
        $managerRole = \Spatie\Permission\Models\Role::findOrCreate('Manager', 'web');
        $managerRole->givePermissionTo([
            'report.view',
            'report.generate',
            'analytics.view',
        ]);
    }
}
