<?php

namespace App\Events;

use App\Models\PayrollRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollStarted
{
    use Dispatchable, SerializesModels;

    public $payrollRun;

    public function __construct(PayrollRun $payrollRun)
    {
        $this->payrollRun = $payrollRun;
    }
}
