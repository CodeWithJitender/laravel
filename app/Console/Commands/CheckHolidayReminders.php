<?php

namespace App\Console\Commands;

use App\Models\HolidayReminder;
use App\Jobs\SendHolidayReminderJob;
use Illuminate\Console\Command;

class CheckHolidayReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holiday:check-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check upcoming holiday reminders and queue their notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking upcoming holiday reminders...');

        $reminders = HolidayReminder::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->get();

        $count = $reminders->count();
        $this->info("Found {$count} pending holiday reminders scheduled to trigger.");

        foreach ($reminders as $reminder) {
            SendHolidayReminderJob::dispatch($reminder->id);
            $this->line("Queued reminder ID {$reminder->id} (Holiday ID {$reminder->holiday_id})");
        }

        $this->info('Completed checking holiday reminders.');
        return Command::SUCCESS;
    }
}
