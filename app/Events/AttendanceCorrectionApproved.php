<?php

namespace App\Events;

use App\Models\AttendanceCorrection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceCorrectionApproved
{
    use Dispatchable, SerializesModels;

    public $correction;

    /**
     * Create a new event instance.
     */
    public function __construct(AttendanceCorrection $correction)
    {
        $this->correction = $correction;
    }
}
