<?php

namespace App\Console\Commands;

use App\Models\ScheduledReport;
use App\Jobs\SendScheduledReportJob;
use Illuminate\Console\Command;

class RunScheduledReports extends Command
{
    protected $signature = 'reports:run-scheduled';

    protected $description = 'Run scheduled reports whose next execution time is due';

    public function handle()
    {
        $this->info('Checking for due scheduled reports...');

        $schedules = ScheduledReport::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('next_run')
                      ->orWhere('next_run', '<=', now());
            })
            ->get();

        $count = $schedules->count();
        $this->info("Found {$count} scheduled reports due for execution.");

        foreach ($schedules as $schedule) {
            $this->line("Dispatching SendScheduledReportJob for schedule ID: {$schedule->id} ({$schedule->reportDefinition->report_name})");
            
            SendScheduledReportJob::dispatch($schedule->id);
        }

        $this->info('Scheduled reports check completed.');
        return Command::SUCCESS;
    }
}
