<?php

namespace App\Events;

use App\Models\Holiday;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HolidayReminderTriggered
{
    use Dispatchable, SerializesModels;

    public $holiday;
    public $daysBefore;

    public function __construct(Holiday $holiday, int $daysBefore)
    {
        $this->holiday = $holiday;
        $this->daysBefore = $daysBefore;
    }
}
