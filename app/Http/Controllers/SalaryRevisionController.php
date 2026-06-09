<?php

namespace App\Http\Controllers;

use App\Services\SalaryRevisionService;
use App\Models\SalaryRevision;
use App\Models\SalaryStructure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Exception;

class SalaryRevisionController extends Controller
{
    protected $revisionService;

    public function __construct(SalaryRevisionService $revisionService)
    {
        $this->revisionService = $revisionService;
    }

    /**
     * Display listing of salary revisions.
     */
    public function index(Request $request)
    {
        if (Gate::denies('payroll.revision.manage')) {
            abort(403);
        }

        $revisions = SalaryRevision::with(['employee.employeeDetail.designation', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $employees = User::where('status', 'active')->get();
        $structures = SalaryStructure::where('status', 'active')->get();

        return view('payroll.revisions.index', compact('revisions', 'employees', 'structures'));
    }

    /**
     * Propose a new salary revision.
     */
    public function store(Request $request)
    {
        if (Gate::denies('payroll.revision.manage')) {
            abort(403);
        }

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'new_gross_salary' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $revision = $this->revisionService->proposeRevision(
                $request->input('employee_id'),
                (float) $request->input('new_gross_salary'),
                $request->input('effective_date'),
                $request->input('reason')
            );

            return redirect()->route('salary-revisions.index')
                ->with('success', 'Salary revision proposed successfully and is pending approval.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve a proposed salary revision.
     */
    public function approve(Request $request, $id)
    {
        if (Gate::denies('payroll.revision.manage')) {
            abort(403);
        }

        $request->validate([
            'salary_structure_id' => 'nullable|exists:salary_structures,id',
        ]);

        try {
            $revision = $this->revisionService->approveRevision(
                $id,
                auth()->id(),
                $request->input('salary_structure_id')
            );

            return redirect()->route('salary-revisions.index')
                ->with('success', 'Salary revision approved successfully and employee structure updated.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
