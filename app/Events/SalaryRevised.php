<?php

namespace App\Events;

use App\Models\SalaryRevision;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalaryRevised
{
    use Dispatchable, SerializesModels;

    public $salaryRevision;

    public function __construct(SalaryRevision $salaryRevision)
    {
        $this->salaryRevision = $salaryRevision;
    }
}
