<?php

namespace App\Events;

use App\Models\Holiday;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HolidayUpdated
{
    use Dispatchable, SerializesModels;

    public $holiday;

    public function __construct(Holiday $holiday)
    {
        $this->holiday = $holiday;
    }
}
