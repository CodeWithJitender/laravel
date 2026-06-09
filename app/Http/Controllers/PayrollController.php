<?php

namespace App\Http\Controllers;

use App\Services\PayrollService;
use App\Models\PayrollRun;
use App\Models\PayrollApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Exception;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Display a listing of the payroll runs.
     */
    public function index(Request $request)
    {
        if (Gate::denies('payroll.view')) {
            abort(403);
        }

        $runs = PayrollRun::orderBy('run_year', 'desc')
            ->orderBy('run_month', 'desc')
            ->paginate(10);

        if ($request->wantsJson()) {
            return response()->json($runs);
        }

        return view('payroll.index', compact('runs'));
    }

    /**
     * Show the form for creating a new payroll run.
     */
    public function create()
    {
        if (Gate::denies('payroll.process')) {
            abort(403);
        }

        return view('payroll.create');
    }

    /**
     * Start a new payroll run.
     */
    public function store(Request $request)
    {
        if (Gate::denies('payroll.process')) {
            abort(403);
        }

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'run_type' => 'required|string|in:monthly,off_cycle,bonus,adjustment',
        ]);

        try {
            $run = $this->payrollService->startPayrollRun(
                $request->input('month'),
                $request->input('year'),
                auth()->id(),
                $request->input('run_type')
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'data' => $run], 201);
            }

            return redirect()->route('payroll.show', $run->id)
                ->with('success', 'Payroll run processing started successfully in background.');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified payroll run details.
     */
    public function show(Request $request, $id)
    {
        if (Gate::denies('payroll.view')) {
            abort(403);
        }

        $run = PayrollRun::with([
            'processor',
            'approver',
            'employees.employee.employeeDetail.designation',
            'employees.employee.employeeDetail.department',
            'approvals.approver'
        ])->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json($run);
        }

        return view('payroll.show', compact('run'));
    }

    /**
     * Approve the payroll run (Finance or HR).
     */
    public function approve(Request $request, $id)
    {
        if (Gate::denies('payroll.approve')) {
            abort(403);
        }

        $request->validate([
            'level' => 'required|string|in:Finance,HR',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            $approval = $this->payrollService->approveRun(
                $id,
                auth()->id(),
                $request->input('level'),
                $request->input('remarks')
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'data' => $approval]);
            }

            return redirect()->route('payroll.show', $id)
                ->with('success', $request->input('level') . ' approved successfully.');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Publish the payroll run.
     */
    public function publish(Request $request, $id)
    {
        if (Gate::denies('payroll.publish')) {
            abort(403);
        }

        try {
            $run = $this->payrollService->publishRun($id, auth()->id());

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'data' => $run]);
            }

            return redirect()->route('payroll.show', $id)
                ->with('success', 'Payroll run published successfully and payslip generation dispatched.');
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
