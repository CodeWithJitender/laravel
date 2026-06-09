<?php

namespace App\Services;

use Carbon\Carbon;

class WorkingHoursEngine
{
    /**
     * Calculate attendance metrics.
     *
     * @param Carbon $in
     * @param Carbon|null $out
     * @param array $shift
     * @param array $officeRules
     * @return array
     */
    public function calculate(Carbon $in, ?Carbon $out, array $shift, array $officeRules): array
    {
        $dateString = $in->toDateString();
        $startTime = Carbon::parse($dateString . ' ' . $shift['start_time']);
        $endTime = Carbon::parse($dateString . ' ' . $shift['end_time']);
        
        // Handle midnight-crossing shifts
        if ($endTime->lessThan($startTime)) {
            $endTime->addDay();
        }

        $grace = $shift['grace_period_minutes'] ?? 15;
        $break = $shift['break_minutes'] ?? 60;
        
        $lateMinutes = 0;
        if ($in->greaterThan($startTime->copy()->addMinutes($grace))) {
            $lateMinutes = abs($in->diffInMinutes($startTime));
        }

        if (!$out) {
            return [
                'worked_hours' => 0.00,
                'late_minutes' => $lateMinutes,
                'early_exit_minutes' => 0,
                'overtime_minutes' => 0,
                'status' => 'Missed Punch'
            ];
        }

        // Worked minutes (subtracting breaks)
        $totalMinutes = abs($out->diffInMinutes($in));
        $workedMins = $totalMinutes - $break;
        $workedHours = max(0, $workedMins) / 60;

        $earlyExitMinutes = 0;
        if ($out->lessThan($endTime)) {
            $earlyExitMinutes = abs($out->diffInMinutes($endTime));
        }

        $overtimeMinutes = 0;
        if ($out->greaterThan($endTime)) {
            $overtimeMinutes = abs($out->diffInMinutes($endTime));
        }

        // Status resolution
        $hFull = $officeRules['minimum_hours'] ?? 8.00;
        $hHalf = $officeRules['half_day_hours'] ?? 4.00;

        if ($workedHours < $hHalf) {
            $status = 'Absent';
        } elseif ($workedHours < $hFull) {
            $status = 'Half Day';
        } elseif ($lateMinutes > 0) {
            $status = 'Late';
        } else {
            $status = 'Present';
        }

        return [
            'worked_hours' => round($workedHours, 2),
            'late_minutes' => $lateMinutes,
            'early_exit_minutes' => $earlyExitMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'status' => $status
        ];
    }
}
