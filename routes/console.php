<?php

use Illuminate\Support\Facades\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('attendance:check-missed')->dailyAt('21:00');
Schedule::command('attendance:mark-absents')->dailyAt('23:59');
Schedule::command('holiday:check-reminders')->dailyAt('09:00');
Schedule::command('leave:accrue')->lastDayOfMonth('23:59');
Schedule::command('leave:carry-forward')->yearlyOn(12, 31, '23:59');
