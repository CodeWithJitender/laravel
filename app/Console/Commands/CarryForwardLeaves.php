<?php

namespace App\Console\Commands;

use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CarryForwardLeaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:carry-forward {year? : Optional year to run the carry forward for}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Process year-end carry forward adjustments and initialize next year\'s balances';

    /**
     * Execute the console command.
     */
    public function handle(LeaveBalanceService $balanceService)
    {
        $year = $this->argument('year') ? (int) $this->argument('year') : Carbon::now()->year;

        $this->info("Starting year-end leave carry-forward run for year: {$year}...");

        try {
            $processedCount = $balanceService->runYearEndCarryForwards($year);
            $this->info("Successfully processed carry forward for {$processedCount} employee leave balances.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to run carry forward: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
