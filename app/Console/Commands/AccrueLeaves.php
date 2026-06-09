<?php

namespace App\Console\Commands;

use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AccrueLeaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:accrue {date? : Optional date to run the accrual for, in YYYY-MM-DD format}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Process monthly leave accruals for active policies and eligible employees';

    /**
     * Execute the console command.
     */
    public function handle(LeaveBalanceService $balanceService)
    {
        $dateString = $this->argument('date');
        $runDate = $dateString ? Carbon::parse($dateString) : Carbon::today();

        $this->info("Starting monthly leave accrual run for date: {$runDate->toDateString()}...");

        try {
            $processedCount = $balanceService->runMonthlyAccruals($runDate);
            $this->info("Successfully processed {$processedCount} leave accrual records.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to run leave accruals: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
