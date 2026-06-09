<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Notifications\MissedPunchNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckMissedPunches extends Command
{
    protected $signature = 'attendance:check-missed';

    protected $description = 'Identify today\'s active punches without clock-outs and mark them as Missed Punch';

    public function handle()
    {
        $this->info('Starting missed punch check...');

        $today = Carbon::today()->toDateString();
        
        $records = Attendance::where('attendance_date', $today)
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->get();

        $count = $records->count();
        $this->info("Found {$count} active punches without clock-out today.");

        foreach ($records as $record) {
            $record->attendance_status = 'Missed Punch';
            $record->save();

            // Send notification to employee
            $record->user->notify(new MissedPunchNotification($record));

            $this->line("Marked missed punch and notified user: {$record->user->name} (ID: {$record->user_id})");
        }

        $this->info('Missed punch check completed successfully.');
        return Command::SUCCESS;
    }
}
