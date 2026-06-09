<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmployeeDetail;
use App\Models\Location;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Shift;
use App\Models\ActivityLog;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if (Gate::denies('employees.view')) {
            abort(403);
        }

        $search = $request->input('search', '');
        $departmentId = $request->input('department_id', '');
        $locationId = $request->input('location_id', '');
        $status = $request->input('status', 'all');

        $query = User::with(['employeeDetail.department', 'employeeDetail.designation', 'employeeDetail.location', 'roles']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('employeeDetail', function ($detailQuery) use ($search) {
                      $detailQuery->where('employee_code', 'like', "%{$search}%")
                                  ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($departmentId) {
            $query->whereHas('employeeDetail', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($locationId) {
            $query->whereHas('employeeDetail', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $employees = $query->paginate(15)->withQueryString();

        $departments = Department::where('status', 'active')->orderBy('department_name')->get();
        $locations = Location::where('status', 'active')->orderBy('location_name')->get();

        if ($request->wantsJson()) {
            return response()->json($employees);
        }

        return view('employees.index', compact('employees', 'departments', 'locations', 'search', 'departmentId', 'locationId', 'status'));
    }

    public function create()
    {
        if (Gate::denies('employees.create')) {
            abort(403);
        }

        $locations = Location::where('status', 'active')->orderBy('location_name')->get();
        $departments = Department::where('status', 'active')->orderBy('department_name')->get();
        $designations = Designation::where('status', 'active')->orderBy('designation_name')->get();
        $shifts = Shift::where('status', 'active')->orderBy('shift_name')->get();
        
        // Potential managers (Admins or Managers)
        $managers = User::role(['Admin', 'Manager'])->where('status', 'active')->orderBy('name')->get();

        return view('employees.create', compact('locations', 'departments', 'designations', 'shifts', 'managers'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
            ]);

            // Assign role
            $user->assignRole($validated['role']);

            // Create EmployeeDetail
            $user->employeeDetail()->create([
                'employee_code' => $validated['employee_code'],
                'joining_date' => $validated['joining_date'],
                'exit_date' => $validated['exit_date'] ?? null,
                'manager_id' => $validated['manager_id'] ?? null,
                'location_id' => $validated['location_id'],
                'department_id' => $validated['department_id'],
                'designation_id' => $validated['designation_id'],
                'shift_id' => $validated['shift_id'],
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_no' => $validated['bank_account_no'] ?? null,
                'pan_no' => $validated['pan_no'] ?? null,
                'gender' => $validated['gender'],
                'dob' => $validated['dob'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);

            return $user;
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $user->load('employeeDetail')], 201);
        }

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Request $request, $id)
    {
        if (Gate::denies('employees.view')) {
            abort(403);
        }

        $employee = User::with([
            'employeeDetail.department', 
            'employeeDetail.designation', 
            'employeeDetail.location', 
            'employeeDetail.shift',
            'employeeDetail.manager',
            'roles',
            'documents',
        ])->findOrFail($id);

        $activities = ActivityLog::where('user_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'employee' => $employee,
                'activities' => $activities,
            ]);
        }

        return view('employees.show', compact('employee', 'activities'));
    }

    public function edit($id)
    {
        if (Gate::denies('employees.edit')) {
            abort(403);
        }

        $employee = User::with('employeeDetail')->findOrFail($id);
        $locations = Location::where('status', 'active')->orderBy('location_name')->get();
        $departments = Department::where('status', 'active')->orderBy('department_name')->get();
        $designations = Designation::where('status', 'active')->orderBy('designation_name')->get();
        $shifts = Shift::where('status', 'active')->orderBy('shift_name')->get();
        
        // Potential managers (Admins or Managers)
        $managers = User::role(['Admin', 'Manager'])->where('status', 'active')->where('id', '!=', $employee->id)->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'locations', 'departments', 'designations', 'shifts', 'managers'));
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        $employee = User::findOrFail($id);
        $validated = $request->validated();

        DB::transaction(function () use ($employee, $validated) {
            // Update user
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $employee->update($userData);

            // Sync role
            $employee->syncRoles([$validated['role']]);

            // Update EmployeeDetail
            $employee->employeeDetail()->updateOrCreate(
                ['user_id' => $employee->id],
                [
                    'employee_code' => $validated['employee_code'],
                    'joining_date' => $validated['joining_date'],
                    'exit_date' => $validated['exit_date'] ?? null,
                    'manager_id' => $validated['manager_id'] ?? null,
                    'location_id' => $validated['location_id'],
                    'department_id' => $validated['department_id'],
                    'designation_id' => $validated['designation_id'],
                    'shift_id' => $validated['shift_id'],
                    'bank_name' => $validated['bank_name'] ?? null,
                    'bank_account_no' => $validated['bank_account_no'] ?? null,
                    'pan_no' => $validated['pan_no'] ?? null,
                    'gender' => $validated['gender'],
                    'dob' => $validated['dob'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                ]
            );
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $employee->load('employeeDetail')]);
        }

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        if (Gate::denies('employees.delete')) {
            abort(403);
        }

        $employee = User::findOrFail($id);
        
        // Prevent admin from deleting themselves
        if ($employee->id === auth()->id()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 400);
            }
            return redirect()->route('employees.index')->with('error', 'You cannot delete your own account.');
        }

        $employee->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Employee deleted successfully.']);
        }

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
