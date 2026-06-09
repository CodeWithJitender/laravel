<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\NotificationRecipient;
use App\Models\NotificationDeliveryLog;
use App\Models\NotificationEvent;
use App\Models\User;
use App\Jobs\SendQueuedNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationService extends BaseService
{
    /**
     * Send a notification from a template key.
     */
    public function sendFromTemplate(string $templateKey, array $data, $audienceType, $audienceValues = null, User $actor = null)
    {
        return $this->transaction(function () use ($templateKey, $data, $audienceType, $audienceValues, $actor) {
            // 1. Fetch template
            $template = NotificationTemplate::where('key', $templateKey)
                ->where('status', 'active')
                ->first();

            if (!$template) {
                // If template is not found, fallback to default custom notification or do nothing
                return null;
            }

            // 2. Compile placeholders in subject & message content
            $subject = $this->compilePlaceholders($template->subject, $data);
            $message = $this->compilePlaceholders($template->content, $data);

            // 3. Resolve audience to list of user IDs
            $recipientIds = $this->resolveAudience($audienceType, $audienceValues);

            if (empty($recipientIds)) {
                return null;
            }

            // 4. Create Notification record
            $notification = Notification::create([
                'title' => $template->name,
                'subject' => $subject,
                'message' => $message,
                'type' => $this->determineTypeFromKey($templateKey),
                'priority' => $this->determinePriorityFromKey($templateKey),
                'channel' => implode(',', $template->channels),
                'status' => 'queued',
                'created_by' => $actor ? $actor->id : null,
                'scheduled_at' => now(),
                'sent_at' => now(),
            ]);

            // 5. Create recipient records and queue jobs
            foreach ($recipientIds as $recipientId) {
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'employee_id' => $recipientId,
                    'status' => 'sent',
                ]);

                // Dispatch to Queue
                SendQueuedNotificationJob::dispatch($notification->id, $recipientId);
            }

            $notification->update(['status' => 'sent']);

            return $notification;
        });
    }

    /**
     * Send a custom custom/manual notification.
     */
    public function sendCustom(array $payload, $audienceType, $audienceValues = null, User $actor = null)
    {
        return $this->transaction(function () use ($payload, $audienceType, $audienceValues, $actor) {
            $recipientIds = $this->resolveAudience($audienceType, $audienceValues);

            if (empty($recipientIds)) {
                return null;
            }

            $channels = $payload['channels'] ?? ['in_app'];

            $notification = Notification::create([
                'title' => $payload['title'] ?? 'System Announcement',
                'subject' => $payload['subject'] ?? 'Notification Alert',
                'message' => $payload['message'],
                'type' => $payload['type'] ?? 'custom',
                'priority' => $payload['priority'] ?? 'medium',
                'channel' => implode(',', $channels),
                'status' => 'queued',
                'created_by' => $actor ? $actor->id : null,
                'scheduled_at' => $payload['scheduled_at'] ?? now(),
                'sent_at' => now(),
            ]);

            foreach ($recipientIds as $recipientId) {
                NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'employee_id' => $recipientId,
                    'status' => 'sent',
                ]);

                SendQueuedNotificationJob::dispatch($notification->id, $recipientId);
            }

            $notification->update(['status' => 'sent']);

            return $notification;
        });
    }

    /**
     * Replace template placeholders with dynamic values.
     */
    public function compilePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $val) {
            $text = str_replace('{{' . $key . '}}', (string) $val, $text);
            $text = str_replace('{{ ' . $key . ' }}', (string) $val, $text);
        }
        return $text;
    }

    /**
     * Resolve various audience definitions into unique user IDs.
     */
    public function resolveAudience(string $audienceType, $values = null): array
    {
        $query = User::where('status', 'active');

        switch ($audienceType) {
            case 'single':
                if (is_numeric($values)) {
                    return [$values];
                }
                if ($values instanceof User) {
                    return [$values->id];
                }
                return [];

            case 'multiple':
                return is_array($values) ? array_map('intval', $values) : [];

            case 'department':
                // Values is department_id or array of ids
                if (is_array($values)) {
                    $query->whereHas('employeeDetail', function ($q) use ($values) {
                        $q->whereIn('department_id', $values);
                    });
                } else {
                    $query->whereHas('employeeDetail', function ($q) use ($values) {
                        $q->where('department_id', $values);
                    });
                }
                break;

            case 'location':
                // Values is location_id or array of ids
                if (is_array($values)) {
                    $query->whereHas('employeeDetail', function ($q) use ($values) {
                        $q->whereIn('location_id', $values);
                    });
                } else {
                    $query->whereHas('employeeDetail', function ($q) use ($values) {
                        $q->where('location_id', $values);
                    });
                }
                break;

            case 'role':
                // Values is role name or array of names
                if (is_array($values)) {
                    $query->whereHas('roles', function ($q) use ($values) {
                        $q->whereIn('name', $values);
                    });
                } else {
                    $query->whereHas('roles', function ($q) use ($values) {
                        $q->where('name', $values);
                    });
                }
                break;

            case 'managers':
                // Retrieve all managers
                $query->whereHas('roles', function ($q) {
                    $q->where('name', 'Manager');
                });
                break;

            case 'all':
                // No filters, sends to everyone active
                break;

            default:
                return [];
        }

        return $query->pluck('id')->toArray();
    }

    /**
     * Log delivery channel states.
     */
    public function logDelivery(int $notificationId, int $employeeId, string $channel, string $status, string $error = null)
    {
        NotificationDeliveryLog::create([
            'notification_id' => $notificationId,
            'employee_id' => $employeeId,
            'channel' => $channel,
            'status' => $status,
            'error_message' => $error,
            'device_info' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Helper to classify type by template key.
     */
    protected function determineTypeFromKey(string $key): string
    {
        if (str_contains($key, 'leave')) return 'leave';
        if (str_contains($key, 'attendance') || str_contains($key, 'punch')) return 'attendance';
        if (str_contains($key, 'payroll')) return 'payroll';
        if (str_contains($key, 'security') || str_contains($key, 'password')) return 'security';
        if (str_contains($key, 'holiday')) return 'holiday';
        if (str_contains($key, 'announcement')) return 'announcement';
        return 'system';
    }

    /**
     * Helper to determine priority by template key.
     */
    protected function determinePriorityFromKey(string $key): string
    {
        if (str_contains($key, 'password') || str_contains($key, 'locked')) return 'critical';
        if (str_contains($key, 'payroll') || str_contains($key, 'released')) return 'high';
        if (str_contains($key, 'approved') || str_contains($key, 'rejected')) return 'medium';
        return 'low';
    }
}
