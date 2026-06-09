<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;
use App\Models\NotificationEvent;
use App\Models\NotificationChannel;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed default channels
        $channels = ['in_app', 'email'];
        foreach ($channels as $channelName) {
            NotificationChannel::updateOrCreate(
                ['name' => $channelName],
                ['is_active' => true]
            );
        }

        // Define default templates and event mappings
        $templates = [
            [
                'key' => 'leave_request_submitted',
                'name' => 'Leave Request Submitted',
                'subject' => 'New Leave Request from {{employee_name}}',
                'content' => '{{employee_name}} has submitted a leave request for {{leave_type}} from {{start_date}} to {{end_date}} (total {{total_days}} days). Reason: {{reason}}',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\LeaveApplied',
            ],
            [
                'key' => 'leave_request_approved',
                'name' => 'Leave Request Approved',
                'subject' => 'Your Leave Request has been Approved',
                'content' => 'Your leave request for {{leave_type}} from {{start_date}} to {{end_date}} has been approved by {{approver_name}}. Remarks: {{remarks}}',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\LeaveApproved',
            ],
            [
                'key' => 'leave_request_rejected',
                'name' => 'Leave Request Rejected',
                'subject' => 'Your Leave Request has been Rejected',
                'content' => 'Your leave request for {{leave_type}} from {{start_date}} to {{end_date}} has been rejected by {{approver_name}}. Remarks: {{remarks}}',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\LeaveRejected',
            ],
            [
                'key' => 'attendance_correction_submitted',
                'name' => 'Attendance Correction Submitted',
                'subject' => 'Attendance Correction Request from {{employee_name}}',
                'content' => '{{employee_name}} has requested attendance correction for {{requested_date}}. Requested Clock In: {{requested_clock_in}}, Clock Out: {{requested_clock_out}}. Reason: {{reason}}',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\AttendanceCorrectionSubmitted',
            ],
            [
                'key' => 'attendance_correction_approved',
                'name' => 'Attendance Correction Approved',
                'subject' => 'Your Attendance Correction Request has been Approved',
                'content' => 'Your attendance correction request for {{requested_date}} ({{requested_clock_in}} - {{requested_clock_out}}) has been approved by {{approver_name}}.',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\AttendanceCorrectionApproved',
            ],
            [
                'key' => 'employee_welcome',
                'name' => 'Employee Welcome',
                'subject' => 'Welcome to the Company, {{employee_name}}!',
                'content' => 'Welcome {{employee_name}} to our team! Your account has been created. Your username is {{employee_email}}. Please log in to complete your onboarding.',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\EmployeeCreated',
            ],

            [
                'key' => 'holiday_published',
                'name' => 'Holiday Published',
                'subject' => 'New Holiday Announced: {{holiday_name}}',
                'content' => 'A new holiday, {{holiday_name}} ({{holiday_date}}), has been announced.',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\HolidayPublished',
            ],
            [
                'key' => 'holiday_reminder',
                'name' => 'Holiday Reminder',
                'subject' => 'Upcoming Holiday: {{holiday_name}}',
                'content' => 'Reminder: {{holiday_name}} ({{holiday_date}}) is upcoming in {{days_before}} days.',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\HolidayReminderTriggered',
            ],
            [
                'key' => 'payroll_published',
                'name' => 'Payroll Published',
                'subject' => 'Your Payslip for {{month_name}} {{year}} is available',
                'content' => 'Dear {{employee_name}}, your payslip for {{month_name}} {{year}} has been generated and published. Net salary credited: {{net_salary}}. You can download it from the portal.',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\PayrollPublished',
            ],
            [
                'key' => 'salary_revised',
                'name' => 'Salary Revised',
                'subject' => 'Your Salary Revision Notification',
                'content' => 'Dear {{employee_name}}, your salary structure has been revised. New gross CTC: {{new_gross}}. Effective date: {{effective_from}}. Reason: {{reason}}',
                'channels' => ['in_app', 'email'],
                'event_class' => 'App\Events\SalaryRevised',
            ],
        ];

        foreach ($templates as $t) {
            $template = NotificationTemplate::updateOrCreate(
                ['key' => $t['key']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $t['name'],
                    'subject' => $t['subject'],
                    'content' => $t['content'],
                    'channels' => $t['channels'],
                    'status' => 'active',
                ]
            );

            NotificationEvent::updateOrCreate(
                ['event_class' => $t['event_class']],
                [
                    'template_id' => $template->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
