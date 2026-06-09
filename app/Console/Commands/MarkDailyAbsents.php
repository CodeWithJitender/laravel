<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use App\Models\OfficeTiming;
use App\Services\HolidayService;
use App\Services\ClockingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkDailyAbsents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absents {date? : Optional date to run the absent marking for, in YYYY-MM-DD format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Identify active employees who did not clock in on a working day and mark them as Absent or Holiday';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily absent marking check...');

        $dateInput = $this->argument('date');
        $date = $dateInput ? Carbon::parse($dateInput) : Carbon::today();
        $dateStr = $date->toDateString();

        $this->info("Processing date: {$dateStr}");

        // Get all active users
        $employees = User::where('status', 'active')->get();

        $holidayService = app(HolidayService::class);
        $clockingService = app(ClockingService::class);
        
        $officeTiming = OfficeTiming::first();
        $weeklyOff = $officeTiming ? $officeTiming->weekly_off : ['Saturday', 'Sunday'];
        $dayName = $date->format('l');
        $isWeeklyOff = is_array($weeklyOff) && in_array($dayName, $weeklyOff);

        $markedAbsent = 0;
        $markedHoliday = 0;

        foreach ($employees as $employee) {
            // Check if attendance record already exists for this date
            $exists = Attendance::where('user_id', $employee->id)
                ->where('attendance_date', $dateStr)
                ->exists();

            if ($exists) {
                // Already has attendance (clocked in, on leave, etc.)
                continue;
            }

            // Check if it's a holiday for this employee's location
            $isHoliday = $holidayService->isHolidayForUserLocation($employee, $dateStr);

            if ($isHoliday) {
                $holidayDetail = $holidayService->getHolidayForUserLocation($employee, $dateStr);
                
                // Create attendance record as Holiday
                Attendance::create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'user_id' => $employee->id,
                    'attendance_date' => $dateStr,
                    'shift_id' => $employee->employeeDetail?->shift_id,
                    'attendance_status' => 'Holiday',
                    'remarks' => $holidayDetail ? "Public Holiday: {$holidayDetail->holiday_name}" : "Public Holiday",
                    'worked_hours' => 0.00,
                    'late_minutes' => 0,
                    'early_exit_minutes' => 0,
                    'overtime_minutes' => 0,
                ]);

                $markedHoliday++;
                $clockingService->updateMonthlySummary($employee->id, $date->month, $date->year);
                continue;
            }

            // If it is weekly off, skip marking them absent
            if ($isWeeklyOff) {
                continue;
            }

            // Otherwise, they are absent
            Attendance::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $employee->id,
                'attendance_date' => $dateStr,
                'shift_id' => $employee->employeeDetail?->shift_id,
                'attendance_status' => 'Absent',
                'remarks' => 'Auto-marked Absent: No clock-in recorded.',
                'worked_hours' => 0.00,
                'late_minutes' => 0,
                'early_exit_minutes' => 0,
                'overtime_minutes' => 0,
            ]);

            $markedAbsent++;
            $clockingService->updateMonthlySummary($employee->id, $date->month, $date->year);
        }

        $this->info("Completed daily absent marking.");
        $this->info("Marked Absent: {$markedAbsent}");
        $this->info("Marked Holiday: {$markedHoliday}");

        return Command::SUCCESS;
    }
}
