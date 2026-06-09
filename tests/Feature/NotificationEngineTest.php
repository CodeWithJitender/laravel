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
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\NotificationRecipient;
use App\Models\NotificationDeliveryLog;
use App\Services\LeaveRequestService;
use App\Jobs\SendQueuedNotificationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Tests\TestCase;

class NotificationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected $location;
    protected $shift;
    protected $department;
    protected $designation;
    protected $employee;
    protected $manager;
    protected $leaveType;
    protected $leavePolicy;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Spatie roles/permissions and Notification templates
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->seed(\Database\Seeders\NotificationSeeder::class);

        $this->location = Location::create([
            'location_name' => 'HQ Delhi',
            'location_code' => 'HQ-DL',
            'status' => 'active',
        ]);

        $this->shift = Shift::create([
            'shift_name' => 'General Shift',
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
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'manager_id' => null,
            'gender' => 'female',
        ]);

        // Create Employee under the Manager
        $this->employee = User::create([
            'name' => 'John Employee',
            'email' => 'employee@company.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $this->employee->assignRole('Employee');

        DB::table('employee_details')->insert([
            'user_id' => $this->employee->id,
            'employee_code' => 'EMP-101',
            'joining_date' => '2026-01-15',
            'location_id' => $this->location->id,
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
            'shift_id' => $this->shift->id,
            'manager_id' => $this->manager->id,
            'gender' => 'male',
        ]);

        // Create Leave Type & Policy
        $this->leaveType = LeaveType::create([
            'name' => 'Casual Leave',
            'code' => 'CL',
            'is_paid' => true,
            'status' => 'active',
        ]);

        $this->leavePolicy = LeavePolicy::create([
            'leave_type_id' => $this->leaveType->id,
            'annual_allocation' => 12.00,
            'monthly_accrual' => false,
            'carry_forward_limit' => 5.00,
            'max_consecutive_days' => 5,
            'notice_period_days' => 0,
            'status' => 'active',
        ]);

        // Initialize balances
        LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'allocated_balance' => 12.00,
            'remaining_balance' => 12.00,
            'pending_approval_balance' => 0.00,
        ]);
    }

    /**
     * Test that leave request submission triggers event, resolves manager audience,
     * and queues the SendQueuedNotificationJob background jobs.
     */
    public function test_leave_submission_triggers_notification_event_and_queues_job()
    {
        Queue::fake();

        $service = app(LeaveRequestService::class);

        $requestData = [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::parse('next Monday')->toDateString(),
            'end_date' => Carbon::parse('next Monday')->toDateString(),
            'reason' => 'Family event',
            'emergency_phone' => '1234567890',
        ];

        // 1. Submit Request
        $leaveRequest = $service->submitRequest($this->employee, $requestData);

        $this->assertNotNull($leaveRequest);

        // 2. Verify notification records are created for the manager
        $notification = Notification::where('title', 'Leave Request Submitted')->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('John Employee', $notification->subject);
        $this->assertStringContainsString('Casual Leave', $notification->message);

        // 3. Verify recipient
        $recipient = NotificationRecipient::where('notification_id', $notification->id)->first();
        $this->assertNotNull($recipient);
        $this->assertEquals($this->manager->id, $recipient->employee_id);

        // 4. Verify Job is queued for manager notification
        Queue::assertPushed(SendQueuedNotificationJob::class, function ($job) use ($notification) {
            return $job->notificationId === $notification->id && $job->employeeId === $this->manager->id;
        });
    }

    /**
     * Test that leave request approval triggers notifications to the employee.
     */
    public function test_leave_approval_triggers_notification_to_employee()
    {
        Queue::fake();

        $service = app(LeaveRequestService::class);

        $requestData = [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => Carbon::parse('next Monday')->toDateString(),
            'end_date' => Carbon::parse('next Monday')->toDateString(),
            'reason' => 'Family event',
            'emergency_phone' => '1234567890',
        ];

        $leaveRequest = $service->submitRequest($this->employee, $requestData);

        // Approve leave request
        $service->approveRequest($leaveRequest->id, $this->manager->id, 'Enjoy your time off!');

        // Verify approved notification exists for the employee
        $notification = Notification::where('title', 'Leave Request Approved')->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('Casual Leave', $notification->message);
        $this->assertStringContainsString('Enjoy your time off!', $notification->message);

        $recipient = NotificationRecipient::where('notification_id', $notification->id)->first();
        $this->assertNotNull($recipient);
        $this->assertEquals($this->employee->id, $recipient->employee_id);

        Queue::assertPushed(SendQueuedNotificationJob::class, function ($job) use ($notification) {
            return $job->notificationId === $notification->id && $job->employeeId === $this->employee->id;
        });
    }

    /**
     * Test notification HTTP actions (list, read, read-all, delete).
     */
    public function test_notification_http_actions()
    {
        // Clear any welcome notifications to isolate HTTP counts
        NotificationRecipient::where('employee_id', $this->employee->id)->delete();

        // Pre-create notification
        $notification = Notification::create([
            'title' => 'Test title',
            'subject' => 'Test subject',
            'message' => 'Test message',
            'type' => 'system',
            'priority' => 'low',
            'channel' => 'in_app',
            'status' => 'sent',
        ]);

        $recipient = NotificationRecipient::create([
            'notification_id' => $notification->id,
            'employee_id' => $this->employee->id,
            'status' => 'sent',
        ]);

        // 1. Fetch notifications list via API
        $response = $this->actingAs($this->employee)
            ->getJson('/notifications');

        $response->assertStatus(200);
        $response->assertJsonFragment(['unread_count' => 1]);

        // 2. Mark notification as read
        $readResponse = $this->actingAs($this->employee)
            ->postJson("/notifications/{$notification->id}/read");

        $readResponse->assertStatus(200);
        $readResponse->assertJson(['success' => true]);

        // Verify state is read
        $this->assertEquals('read', $recipient->fresh()->status);

        // 3. Mark all as read
        $readAllResponse = $this->actingAs($this->employee)
            ->postJson("/notifications/read-all");

        $readAllResponse->assertStatus(200);
        $readAllResponse->assertJson(['success' => true]);

        // 4. Delete notification recipient
        $deleteResponse = $this->actingAs($this->employee)
            ->deleteJson("/notifications/{$notification->id}");

        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        $this->assertNull(NotificationRecipient::find($recipient->id));
    }

    /**
     * Test template placeholder compilation helper.
     */
    public function test_compilation_helper()
    {
        $service = app(\App\Services\NotificationService::class);
        $template = "Hello {{employee_name}}, your request for {{leave_type}} is ready.";
        
        $compiled = $service->compilePlaceholders($template, [
            'employee_name' => 'John',
            'leave_type' => 'CL',
        ]);

        $this->assertEquals("Hello John, your request for CL is ready.", $compiled);
    }
}
