<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Mail\DynamicMail;
use App\Services\NotificationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendQueuedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $notificationId;
    public $employeeId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $notificationId, int $employeeId)
    {
        $this->notificationId = $notificationId;
        $this->employeeId = $employeeId;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService)
    {
        $notification = Notification::find($this->notificationId);
        $employee = User::find($this->employeeId);

        if (!$notification || !$employee) {
            return;
        }

        // Get channels
        $channels = array_filter(explode(',', $notification->channel ?? 'in_app'));

        foreach ($channels as $channel) {
            $channel = trim($channel);
            
            try {
                if ($channel === 'email') {
                    if ($employee->email) {
                        Mail::to($employee->email)->send(
                            new DynamicMail($notification->subject, $notification->message)
                        );
                        
                        $notificationService->logDelivery(
                            $this->notificationId, 
                            $this->employeeId, 
                            'email', 
                            'sent'
                        );
                    }
                } elseif ($channel === 'in_app') {
                    // Update recipient record
                    NotificationRecipient::where('notification_id', $this->notificationId)
                        ->where('employee_id', $this->employeeId)
                        ->update([
                            'status' => 'delivered',
                            'delivered_at' => now(),
                        ]);

                    $notificationService->logDelivery(
                        $this->notificationId, 
                        $this->employeeId, 
                        'in_app', 
                        'sent'
                    );
                }
            } catch (Exception $e) {
                // Log failure
                $notificationService->logDelivery(
                    $this->notificationId, 
                    $this->employeeId, 
                    $channel, 
                    'failed', 
                    $e->getMessage()
                );
            }
        }
    }
}
