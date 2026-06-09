<?php

namespace App\Http\Controllers;

use App\Models\SalaryStructure;
use App\Models\SalaryComponent;
use App\Models\EmployeeSalaryStructure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Exception;

class SalaryStructureController extends Controller
{
    /**
     * Display a listing of structures and components.
     */
    public function index(Request $request)
    {
        if (Gate::denies('payroll.structure.manage')) {
            abort(403);
        }

        $structures = SalaryStructure::with('components')->get();
        $components = SalaryComponent::all();

        return view('payroll.structures.index', compact('structures', 'components'));
    }

    /**
     * Store a new salary structure.
     */
    public function store(Request $request)
    {
        if (Gate::denies('payroll.structure.manage')) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:salary_structures,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $structure = SalaryStructure::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => 'active',
        ]);

        return redirect()->route('salary-structures.index')
            ->with('success', 'Salary structure blueprint created successfully.');
    }

    /**
     * Update structure components pivot.
     */
    public function updateComponents(Request $request, $id)
    {
        if (Gate::denies('payroll.structure.manage')) {
            abort(403);
        }

        $structure = SalaryStructure::findOrFail($id);
        
        $request->validate([
            'components' => 'required|array',
            'components.*.id' => 'required|exists:salary_components,id',
            'components.*.calculation_value' => 'required|numeric|min:0',
            'components.*.sort_order' => 'required|integer|min:0',
        ]);

        $syncData = [];
        foreach ($request->input('components') as $comp) {
            $syncData[$comp['id']] = [
                'calculation_value' => $comp['calculation_value'],
                'sort_order' => $comp['sort_order'],
            ];
        }

        $structure->components()->sync($syncData);

        return redirect()->route('salary-structures.index')
            ->with('success', 'Components associated with structure successfully.');
    }

    /**
     * Renders assigning structures form.
     */
    public function assignForm()
    {
        if (Gate::denies('payroll.structure.manage')) {
            abort(403);
        }

        $employees = User::where('status', 'active')->get();
        $structures = SalaryStructure::where('status', 'active')->get();

        return view('payroll.structures.assign', compact('employees', 'structures'));
    }

    /**
     * Save employee structure assignment.
     */
    public function storeAssignment(Request $request)
    {
        if (Gate::denies('payroll.structure.manage')) {
            abort(403);
        }

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'salary_structure_id' => 'required|exists:salary_structures,id',
            'effective_from' => 'required|date',
            'monthly_gross_salary' => 'required|numeric|min:0',
        ]);

        try {
            // Deactivate any currently active structures
            EmployeeSalaryStructure::where('employee_id', $request->input('employee_id'))
                ->where('status', 'active')
                ->update(['status' => 'inactive', 'effective_to' => now()->subDay()->toDateString()]);

            $gross = $request->input('monthly_gross_salary');

            EmployeeSalaryStructure::create([
                'employee_id' => $request->input('employee_id'),
                'salary_structure_id' => $request->input('salary_structure_id'),
                'effective_from' => $request->input('effective_from'),
                'effective_to' => null,
                'monthly_gross_salary' => $gross,
                'annual_ctc' => $gross * 12,
                'status' => 'active',
            ]);

            return redirect()->route('salary-structures.index')
                ->with('success', 'Salary structure assigned to employee successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
