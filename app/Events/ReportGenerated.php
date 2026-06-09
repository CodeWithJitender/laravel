<?php

namespace App\Events;

use App\Models\ReportExport;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportGenerated
{
    use Dispatchable, SerializesModels;

    public $reportExport;

    public function __construct(ReportExport $reportExport)
    {
        $this->reportExport = $reportExport;
    }
}
