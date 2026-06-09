<?php

namespace App\Services;

use App\Models\Payslip;
use Carbon\Carbon;

class PdfService
{
    /**
     * Render the premium print-ready HTML for a payslip.
     */
    public function renderPayslipHtml(Payslip $payslip): string
    {
        $payslip->loadMissing([
            'employee.employeeDetail.location',
            'employee.employeeDetail.department',
            'employee.employeeDetail.designation',
            'payrollRunEmployee.items'
        ]);

        $employee = $payslip->employee;
        $detail = $employee->employeeDetail;
        $runEmployee = $payslip->payrollRunEmployee;
        $run = $runEmployee->payrollRun;
        $items = $runEmployee->items;

        $monthName = Carbon::createFromDate($run->run_year, $run->run_month, 1)->format('F Y');

        $earnings = $items->where('component_type', 'earning');
        $deductions = $items->where('component_type', 'deduction');

        $netSalaryWords = $this->numberToWords((float) $payslip->net_salary);

        // Styling for a premium corporate payslip
        $html = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Payslip - ' . $payslip->reference_no . '</title>
            <style>
                body {
                    font-family: \'Inter\', \'Segoe UI\', Roboto, sans-serif;
                    color: #1e293b;
                    background-color: #ffffff;
                    margin: 0;
                    padding: 20px;
                    font-size: 13px;
                    line-height: 1.5;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    border: 1px solid #e2e8f0;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
                }
                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 2px solid #3b82f6;
                    padding-bottom: 20px;
                    margin-bottom: 20px;
                }
                .logo-container h1 {
                    margin: 0;
                    font-size: 24px;
                    color: #1e3a8a;
                    font-weight: 800;
                    letter-spacing: -0.025em;
                }
                .logo-container p {
                    margin: 2px 0 0 0;
                    color: #64748b;
                    font-size: 11px;
                    text-transform: uppercase;
                }
                .title-container {
                    text-align: right;
                }
                .title-container h2 {
                    margin: 0;
                    font-size: 18px;
                    color: #0f172a;
                    font-weight: 700;
                }
                .title-container p {
                    margin: 5px 0 0 0;
                    font-weight: 600;
                    color: #3b82f6;
                    font-size: 13px;
                }
                .meta-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 20px;
                    background-color: #f8fafc;
                    padding: 15px;
                    border-radius: 6px;
                }
                .meta-col p {
                    margin: 4px 0;
                }
                .meta-col strong {
                    color: #334155;
                }
                .table-container {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 0;
                    border: 1px solid #cbd5e1;
                    border-radius: 6px;
                    overflow: hidden;
                    margin-bottom: 20px;
                }
                .column-box {
                    padding: 0;
                }
                .column-box:first-child {
                    border-right: 1px solid #cbd5e1;
                }
                .table-header {
                    background-color: #f1f5f9;
                    font-weight: 700;
                    padding: 10px 15px;
                    border-bottom: 1px solid #cbd5e1;
                    color: #1e293b;
                    display: flex;
                    justify-content: space-between;
                }
                .row-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 15px;
                    border-bottom: 1px dashed #e2e8f0;
                }
                .row-item:last-child {
                    border-bottom: none;
                }
                .totals-bar {
                    display: flex;
                    justify-content: space-between;
                    background-color: #f8fafc;
                    padding: 10px 15px;
                    border-top: 1px solid #cbd5e1;
                    font-weight: 700;
                }
                .net-salary-section {
                    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
                    color: #ffffff;
                    padding: 20px;
                    border-radius: 6px;
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .net-salary-box h3 {
                    margin: 0;
                    font-size: 14px;
                    opacity: 0.9;
                    font-weight: 500;
                }
                .net-salary-box h2 {
                    margin: 5px 0 0 0;
                    font-size: 26px;
                    font-weight: 800;
                }
                .words-box {
                    text-align: right;
                    max-width: 60%;
                }
                .words-box p {
                    margin: 0;
                    font-size: 12px;
                    font-style: italic;
                    opacity: 0.95;
                }
                .footer {
                    margin-top: 30px;
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-end;
                }
                .sign-box {
                    text-align: center;
                    width: 200px;
                }
                .sign-line {
                    border-bottom: 1px solid #94a3b8;
                    margin-bottom: 5px;
                    height: 50px;
                }
                .security-hash {
                    font-size: 9px;
                    color: #94a3b8;
                    font-family: monospace;
                    max-width: 400px;
                    word-break: break-all;
                }
                @media print {
                    body {
                        padding: 0;
                    }
                    .container {
                        border: none;
                        box-shadow: none;
                        padding: 0;
                    }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo-container">
                        <h1>' . config('app.name', 'HRMS Enterprise') . '</h1>
                        <p>Corporate OfficeSetup & HQ</p>
                    </div>
                    <div class="title-container">
                        <h2>PAYSLIP</h2>
                        <p>' . $monthName . '</p>
                    </div>
                </div>

                <div class="meta-grid">
                    <div class="meta-col">
                        <p><strong>Employee Name:</strong> ' . $employee->name . '</p>
                        <p><strong>Employee ID:</strong> EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) . '</p>
                        <p><strong>Designation:</strong> ' . ($detail?->designation?->designation_name ?? 'N/A') . '</p>
                        <p><strong>Department:</strong> ' . ($detail?->department?->department_name ?? 'N/A') . '</p>
                        <p><strong>Location:</strong> ' . ($detail?->location?->location_name ?? 'N/A') . '</p>
                    </div>
                    <div class="meta-col">
                        <p><strong>Payslip Reference:</strong> ' . $payslip->reference_no . '</p>
                        <p><strong>Payment Period:</strong> ' . $monthName . '</p>
                        <p><strong>Working Days in Month:</strong> ' . $runEmployee->total_working_days . '</p>
                        <p><strong>Paid Days:</strong> ' . $runEmployee->paid_days . '</p>
                        <p><strong>LOP Days:</strong> ' . $runEmployee->lop_days . '</p>
                    </div>
                </div>

                <div class="table-container">
                    <!-- Earnings Column -->
                    <div class="column-box">
                        <div class="table-header">
                            <span>EARNINGS</span>
                            <span>AMOUNT</span>
                        </div>
                        <div style="min-height: 200px;">';
                        foreach ($earnings as $earn) {
                            $html .= '
                            <div class="row-item">
                                <span>' . $earn->component_name . '</span>
                                <strong>' . number_format($earn->amount, 2) . '</strong>
                            </div>';
                        }
                        $html .= '
                        </div>
                        <div class="totals-bar">
                            <span>Total Earnings</span>
                            <span>' . number_format($payslip->total_earnings, 2) . '</span>
                        </div>
                    </div>

                    <!-- Deductions Column -->
                    <div class="column-box">
                        <div class="table-header">
                            <span>DEDUCTIONS</span>
                            <span>AMOUNT</span>
                        </div>
                        <div style="min-height: 200px;">';
                        foreach ($deductions as $ded) {
                            $html .= '
                            <div class="row-item">
                                <span>' . $ded->component_name . '</span>
                                <strong>' . number_format($ded->amount, 2) . '</strong>
                            </div>';
                        }
                        $html .= '
                        </div>
                        <div class="totals-bar">
                            <span>Total Deductions</span>
                            <span>' . number_format($payslip->total_deductions, 2) . '</span>
                        </div>
                    </div>
                </div>

                <div class="net-salary-section">
                    <div class="net-salary-box">
                        <h3>NET PAYABLE SALARY</h3>
                        <h2>₹' . number_format($payslip->net_salary, 2) . '</h2>
                    </div>
                    <div class="words-box">
                        <p><strong>In Words:</strong> ' . $netSalaryWords . '</p>
                    </div>
                </div>

                <div class="footer">
                    <div class="security-hash">
                        System generated payslip. Secure Hash: ' . $payslip->secure_hash . '
                    </div>
                    <div class="sign-box">
                        <div class="sign-line"></div>
                        <strong>Authorized Signatory</strong>
                        <p style="margin: 2px 0 0 0; font-size: 10px; color: #64748b;">Human Resources Department</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ';

        return $html;
    }

    /**
     * Basic helper to convert number to words.
     */
    protected function numberToWords(float $amount): string
    {
        $number = (int) $amount;
        $fraction = round(($amount - $number) * 100);

        $words = $this->convertIntegerToWords($number);
        
        $output = $words . ' Rupees';

        if ($fraction > 0) {
            $output .= ' and ' . $this->convertIntegerToWords($fraction) . ' Paisa';
        }

        return $output . ' Only';
    }

    protected function convertIntegerToWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
        ];

        $tens = [
            '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];

        $triplets = [
            '', 'Thousand', 'Million', 'Billion'
        ];

        $words = [];
        $tripletCount = 0;

        while ($number > 0) {
            $remainder = $number % 1000;
            $number = (int) ($number / 1000);

            if ($remainder > 0) {
                $tripletWords = [];
                
                $hundreds = (int) ($remainder / 100);
                $tensAndOnes = $remainder % 100;

                if ($hundreds > 0) {
                    $tripletWords[] = $ones[$hundreds] . ' Hundred';
                }

                if ($tensAndOnes > 0) {
                    if ($tensAndOnes < 20) {
                        $tripletWords[] = $ones[$tensAndOnes];
                    } else {
                        $t = (int) ($tensAndOnes / 10);
                        $o = $tensAndOnes % 10;
                        $tripletWords[] = $tens[$t] . ($o > 0 ? '-' . $ones[$o] : '');
                    }
                }

                $tripletString = implode(' ', $tripletWords);
                if ($tripletCount > 0) {
                    $tripletString .= ' ' . $triplets[$tripletCount];
                }

                array_unshift($words, $tripletString);
            }

            $tripletCount++;
        }

        return implode(' ', $words);
    }
}
