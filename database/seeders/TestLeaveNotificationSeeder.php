<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EmployeeDetail;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\LeaveRequestDay;
use App\Models\LeaveStatusHistory;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Shift;
use App\Services\LeaveBalanceService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestLeaveNotificationSeeder extends Seeder
{
    /**
     * Create test employee "Jitendra" and generate a sick leave notification for the admin.
     */
    public function run(): void
    {
        // 1. Find or create Jitendra
        $jitendra = User::where('email', 'jitendra@company.com')->first();

        if (!$jitendra) {
            $jitendra = User::create([
                'name' => 'Jitendra',
                'email' => 'jitendra@company.com',
                'password' => Hash::make('password'),
                'status' => 'active',
                'uuid' => (string) Str::uuid(),
            ]);

            $jitendra->assignRole('Employee');

            $this->command->info('✅ Employee "Jitendra" (jitendra@company.com) created successfully.');
        } else {
            $this->command->info('ℹ️  Employee "Jitendra" already exists, skipping user creation.');
        }

        // Ensure EmployeeDetail exists (handles partial previous runs)
        if (!$jitendra->employeeDetail) {
            $department = Department::first();
            $designation = Designation::where('designation_name', 'Software Engineer')->first() ?? Designation::first();
            $location = Location::first();
            $shift = Shift::first();
            $admin = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))->first();

            EmployeeDetail::create([
                'user_id' => $jitendra->id,
                'employee_code' => 'EMP-004',
                'joining_date' => Carbon::now()->subMonths(3),
                'department_id' => $department?->id,
                'designation_id' => $designation?->id,
                'location_id' => $location?->id,
                'shift_id' => $shift?->id,
                'manager_id' => $admin?->id,
                'gender' => 'male',
                'dob' => Carbon::parse('1995-03-15'),
                'phone' => '9876543210',
            ]);
            $this->command->info('✅ Employee detail created for Jitendra.');
        }

        // Ensure leave balances exist
        $leaveTypes = LeaveType::where('status', 'active')->get();
        foreach ($leaveTypes as $leaveType) {
            LeaveBalance::firstOrCreate(
                ['employee_id' => $jitendra->id, 'leave_type_id' => $leaveType->id],
                [
                    'opening_balance' => 0,
                    'allocated_balance' => 12,
                    'accrued_balance' => 0,
                    'used_balance' => 0,
                    'pending_balance' => 0,
                    'carry_forward_balance' => 0,
                ]
            );
        }

        // 2. Create a pending sick leave request
        $sickLeave = LeaveType::where('code', 'SL')
            ->orWhere('name', 'like', '%Sick%')
            ->first();

        if (!$sickLeave) {
            $sickLeave = LeaveType::where('status', 'active')->first();
            $this->command->warn('⚠️  No "Sick Leave" type found. Using: ' . $sickLeave->name);
        }

        $startDate = Carbon::tomorrow();
        $endDate = Carbon::tomorrow()->addDays(2);
        $totalDays = 3;

        $leaveRequest = LeaveRequest::create([
            'uuid' => (string) Str::uuid(),
            'employee_id' => $jitendra->id,
            'leave_type_id' => $sickLeave->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'half_day' => false,
            'reason' => 'Feeling unwell due to high fever and body aches. Need rest as per doctor\'s advice for recovery.',
            'emergency_phone' => '9876543210',
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        // Create leave request day breakdown
        for ($i = 0; $i < $totalDays; $i++) {
            LeaveRequestDay::create([
                'leave_request_id' => $leaveRequest->id,
                'leave_date' => $startDate->copy()->addDays($i),
                'day_weight' => 1.0,
                'session' => 'full_day',
            ]);
        }

        // Create status history
        LeaveStatusHistory::create([
            'leave_request_id' => $leaveRequest->id,
            'user_id' => $jitendra->id,
            'status' => 'pending',
            'remarks' => 'Leave request submitted.',
        ]);

        // Update pending balance
        $balance = LeaveBalance::where('employee_id', $jitendra->id)
            ->where('leave_type_id', $sickLeave->id)
            ->first();
        if ($balance) {
            $balance->increment('pending_balance', $totalDays);
        }

        $this->command->info('✅ Sick leave request created (ID: ' . $leaveRequest->id . ')');

        // 3. Create a notification for the admin/manager
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))->first();

        if ($admin) {
            $notification = Notification::create([
                'uuid' => (string) Str::uuid(),
                'title' => 'Leave Request Submitted',
                'subject' => 'New Leave Request from Jitendra',
                'message' => "Jitendra has submitted a leave request for {$sickLeave->name} from {$startDate->toDateString()} to {$endDate->toDateString()} (total {$totalDays} days). Reason: Feeling unwell due to high fever and body aches. Need rest as per doctor's advice for recovery.",
                'type' => 'leave',
                'priority' => 'high',
                'channel' => 'in_app',
                'action_url' => '/leave/' . $leaveRequest->id,
                'status' => 'sent',
                'created_by' => $jitendra->id,
                'scheduled_at' => now(),
                'sent_at' => now(),
            ]);

            NotificationRecipient::create([
                'notification_id' => $notification->id,
                'employee_id' => $admin->id,
                'status' => 'sent',
            ]);

            $this->command->info('✅ Leave notification created for admin (notification ID: ' . $notification->id . ')');
            $this->command->info('');
            $this->command->info('🧪 TEST INSTRUCTIONS:');
            $this->command->info('   1. Login as admin: admin@company.com / password');
            $this->command->info('   2. Click the notification bell icon');
            $this->command->info('   3. Click the leave notification from Jitendra');
            $this->command->info('   4. You should be redirected to the leave review page');
        } else {
            $this->command->warn('⚠️  No admin user found. Notification was not created.');
        }
    }
}
