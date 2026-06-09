<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Services\DepartmentService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DepartmentController extends Controller
{
    protected $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index(Request $request)
    {
        if (Gate::denies('department.view')) {
            abort(403);
        }

        $search = $request->input('search', '');
        $status = $request->input('status', 'all');
        $departments = $this->departmentService->getPaginated(10, $search, $status);

        if ($request->wantsJson()) {
            return response()->json($departments);
        }

        return view('organization.departments.index', compact('departments', 'search', 'status'));
    }

    public function create()
    {
        if (Gate::denies('department.create')) {
            abort(403);
        }

        $employees = User::where('status', 'active')->orderBy('name')->get();

        return view('organization.departments.create', compact('employees'));
    }

    public function store(StoreDepartmentRequest $request)
    {
        $department = $this->departmentService->createDepartment($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $department], 201);
        }

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function show(Request $request, $id)
    {
        if (Gate::denies('department.view')) {
            abort(403);
        }

        $department = $this->departmentService->findById($id);

        if ($request->wantsJson()) {
            return response()->json($department);
        }

        return view('organization.departments.show', compact('department'));
    }

    public function edit($id)
    {
        if (Gate::denies('department.edit')) {
            abort(403);
        }

        $department = $this->departmentService->findById($id);
        $employees = User::where('status', 'active')->orderBy('name')->get();

        return view('organization.departments.edit', compact('department', 'employees'));
    }

    public function update(UpdateDepartmentRequest $request, $id)
    {
        $this->departmentService->updateDepartment($id, $request->validated());

        if ($request->wantsJson()) {
            $department = $this->departmentService->findById($id);
            return response()->json(['success' => true, 'data' => $department]);
        }

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        if (Gate::denies('department.delete')) {
            abort(403);
        }

        try {
            $this->departmentService->deleteDepartment($id);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Department deleted successfully.']);
            }

            return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->route('departments.index')->with('error', $e->getMessage());
        }
    }
}
