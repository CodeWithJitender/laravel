<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Repositories\PayrollRepositoryInterface;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PayslipController extends Controller
{
    protected $payrollRepository;
    protected $pdfService;

    public function __construct(PayrollRepositoryInterface $payrollRepository, PdfService $pdfService)
    {
        $this->payrollRepository = $payrollRepository;
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of payslips.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if (Gate::allows('payroll.view')) {
            // Admin/HR view: see all payslips
            $payslips = Payslip::with(['employee.employeeDetail.designation', 'employee.employeeDetail.department'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } elseif (Gate::allows('payroll.payslip.view_self')) {
            // Employee view: see own payslips
            $payslips = Payslip::where('employee_id', $user->id)
                ->whereNotNull('published_at')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            abort(403);
        }

        return view('payroll.payslips.index', compact('payslips'));
    }

    /**
     * Display the details of a specific payslip.
     */
    public function show($id)
    {
        $payslip = $this->payrollRepository->getPayslipById($id);

        if (!$payslip) {
            abort(404);
        }

        // Authorization check
        if (auth()->id() !== $payslip->employee_id && Gate::denies('payroll.view')) {
            abort(403);
        }

        return view('payroll.payslips.show', compact('payslip'));
    }

    /**
     * Download or view print version of a payslip.
     */
    public function download($id)
    {
        $payslip = $this->payrollRepository->getPayslipById($id);

        if (!$payslip) {
            abort(404);
        }

        // Authorization check
        if (auth()->id() !== $payslip->employee_id && Gate::denies('payroll.view')) {
            abort(403);
        }

        $html = $this->pdfService->renderPayslipHtml($payslip);

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="payslip_' . $payslip->reference_no . '.html"');
    }
}
