<?php

namespace App\Jobs;

use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Services\PdfService;
use App\Events\PayslipGenerated;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class GeneratePayslipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payrollRunId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $payrollRunId)
    {
        $this->payrollRunId = $payrollRunId;
    }

    /**
     * Execute the job.
     */
    public function handle(PdfService $pdfService)
    {
        $payrollRun = PayrollRun::with('employees.employee')->find($this->payrollRunId);
        if (!$payrollRun) {
            return;
        }

        foreach ($payrollRun->employees as $runEmployee) {
            // Check if payslip already exists for this run employee
            $payslip = Payslip::where('payroll_run_employee_id', $runEmployee->id)->first();

            if (!$payslip) {
                // Generate a reference number: e.g., PS-YEAR-MONTH-EMPLOYEE
                $refNo = 'PS-' . $payrollRun->run_year . '-' . str_pad($payrollRun->run_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($runEmployee->employee_id, 4, '0', STR_PAD_LEFT);
                
                // If by some chance the reference number is not unique, append random string
                if (Payslip::where('reference_no', $refNo)->exists()) {
                    $refNo .= '-' . strtoupper(Str::random(4));
                }

                $payslip = Payslip::create([
                    'uuid' => (string) Str::uuid(),
                    'payroll_run_employee_id' => $runEmployee->id,
                    'employee_id' => $runEmployee->employee_id,
                    'reference_no' => $refNo,
                    'gross_salary' => $runEmployee->monthly_gross_salary,
                    'total_earnings' => $runEmployee->total_earnings,
                    'total_deductions' => $runEmployee->total_deductions,
                    'net_salary' => $runEmployee->net_salary,
                    'generated_at' => now(),
                    'secure_hash' => hash('sha256', Str::random(40)),
                ]);
            }

            // Generate HTML using PdfService
            $html = $pdfService->renderPayslipHtml($payslip);

            // Store HTML or PDF path
            // Let's store it under storage/app/public/payslips/
            $fileName = 'payslip_' . $payslip->uuid . '.html';
            Storage::disk('public')->put('payslips/' . $fileName, $html);

            $payslip->update([
                'pdf_path' => 'storage/payslips/' . $fileName,
            ]);

            event(new PayslipGenerated($payslip));
        }
    }
}
