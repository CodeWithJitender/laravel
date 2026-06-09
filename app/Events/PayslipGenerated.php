<?php

namespace App\Events;

use App\Models\Payslip;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayslipGenerated
{
    use Dispatchable, SerializesModels;

    public $payslip;

    public function __construct(Payslip $payslip)
    {
        $this->payslip = $payslip;
    }
}
