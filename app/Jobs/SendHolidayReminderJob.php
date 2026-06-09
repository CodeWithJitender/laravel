<?php

namespace App\Jobs;

use App\Models\HolidayReminder;
use App\Events\HolidayReminderTriggered;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendHolidayReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reminderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $reminderId)
    {
        $this->reminderId = $reminderId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $reminder = HolidayReminder::with('holiday')->find($this->reminderId);

        if (!$reminder || $reminder->status !== 'pending') {
            return;
        }

        try {
            // Trigger the reminder event which will generate and send notifications to all employees
            event(new HolidayReminderTriggered($reminder->holiday, $reminder->reminder_days_before));

            $reminder->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Exception $e) {
            $reminder->update([
                'status' => 'failed',
            ]);
            throw $e;
        }
    }
}
