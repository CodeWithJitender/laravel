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
        $designations = Designation::where('status', 'active')->orderBy('designation_name')->get();
        $shifts = Shift::where('status', 'active')->orderBy('shift_name')->get();

        if ($request->wantsJson()) {
            return response()->json($employees);
        }

        return view('employees.index', compact('employees', 'departments', 'locations', 'designations', 'shifts', 'search', 'departmentId', 'locationId', 'status'));
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

        DB::transaction(function () use ($employee) {
            $employee->delete();
            $employee->employeeDetail()->delete();
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Employee deleted successfully.']);
        }

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function downloadTemplate()
    {
        if (Gate::denies('employees.create')) {
            abort(403);
        }

        // Get some active options to populate the demo row
        $dept = Department::where('status', 'active')->first()?->department_name ?? 'Engineering';
        $desig = Designation::where('status', 'active')->first()?->designation_name ?? 'Software Engineer';
        $loc = Location::where('status', 'active')->first()?->location_name ?? 'Headquarters';
        $shift = Shift::where('status', 'active')->first()?->shift_name ?? 'Regular Shift';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_import_template.csv"',
        ];

        $columns = [
            'Name', 'Email', 'Password', 'Role', 'Status', 'Employee Code', 
            'Joining Date', 'Gender', 'Phone', 'Date of Birth', 
            'Department', 'Designation', 'Location', 'Shift', 'Manager Email',
            'Bank Name', 'Bank Account No', 'PAN No'
        ];

        $demoRow = [
            'John Doe', 'john.doe@example.com', 'Welcome@123', 'Employee', 'active', 'EMP-999',
            date('Y-m-d'), 'male', '1234567890', '1995-05-15',
            $dept, $desig, $loc, $shift, 'manager@company.com',
            'Chase Bank', '123456789', 'ABCDE1234F'
        ];

        return response()->stream(function () use ($columns, $demoRow) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $demoRow);
            fclose($file);
        }, 200, $headers);
    }

    public function import(Request $request)
    {
        if (Gate::denies('employees.create')) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return redirect()->back()->with('error', 'Unable to open the uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'The uploaded file is empty.');
        }

        // Remove UTF-8 BOM if present
        if (strpos($header[0], "\xEF\xBB\xBF") === 0) {
            $header[0] = substr($header[0], 3);
        }

        // Clean headers: lowercase and remove spaces/underscores/hyphens
        $headers = array_map(function ($h) {
            return strtolower(str_replace([' ', '_', '-'], '', trim($h)));
        }, $header);

        // Map cached database lookups for speed
        $departmentsCache = Department::withTrashed()->pluck('id', 'department_name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        $designationsCache = Designation::withTrashed()->pluck('id', 'designation_name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        $locationsCache = Location::withTrashed()->pluck('id', 'location_name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        $shiftsCache = Shift::withTrashed()->pluck('id', 'shift_name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        $successCount = 0;
        $errors = [];
        $rowNumber = 1; // Row 1 is header

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            // Skip empty rows
            if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                continue;
            }

            // Map row fields by header key safely
            $data = [];
            foreach ($headers as $index => $key) {
                $data[$key] = isset($row[$index]) ? trim($row[$index]) : '';
            }

            $rowErrors = [];

            // Extract variables
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $roleName = $data['role'] ?? 'Employee';
            $status = strtolower($data['status'] ?? 'active');
            $employeeCode = $data['employeecode'] ?? '';
            $joiningDate = $data['joiningdate'] ?? '';
            $gender = strtolower($data['gender'] ?? '');
            $phone = $data['phone'] ?? null;
            $dob = $data['dateofbirth'] ?? $data['dob'] ?? null;
            $deptName = strtolower($data['department'] ?? '');
            $desigName = strtolower($data['designation'] ?? '');
            $locName = strtolower($data['location'] ?? '');
            $shiftName = strtolower($data['shift'] ?? '');
            $managerEmail = $data['manageremail'] ?? '';
            $bankName = $data['bankname'] ?? null;
            $bankAccountNo = $data['bankaccountno'] ?? null;
            $panNo = $data['panno'] ?? null;

            // Validate
            if (empty($name)) {
                $rowErrors[] = "Name is required.";
            }

            if (empty($email)) {
                $rowErrors[] = "Email is required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Email format is invalid.";
            } else {
                $existingUser = User::withTrashed()->where('email', $email)->first();
                if ($existingUser) {
                    if (!$existingUser->trashed()) {
                        $rowErrors[] = "Email '{$email}' is already taken.";
                    }
                }
            }

            if (empty($employeeCode)) {
                $rowErrors[] = "Employee Code is required.";
            } else {
                $existingDetail = EmployeeDetail::withTrashed()->where('employee_code', $employeeCode)->first();
                if ($existingDetail) {
                    $associatedUser = User::withTrashed()->find($existingDetail->user_id);
                    if ($associatedUser && $associatedUser->email !== $email) {
                        $rowErrors[] = "Employee Code '{$employeeCode}' is already taken.";
                    }
                }
            }

            if (empty($joiningDate)) {
                $rowErrors[] = "Joining Date is required.";
            } elseif (!strtotime($joiningDate)) {
                $rowErrors[] = "Joining Date must be a valid date.";
            } else {
                $joiningDate = date('Y-m-d', strtotime($joiningDate));
            }

            if (!in_array($gender, ['male', 'female', 'other'])) {
                $rowErrors[] = "Gender must be male, female, or other.";
            }

            $roleName = ucfirst(strtolower($roleName));
            if (!in_array($roleName, ['Admin', 'Manager', 'Employee'])) {
                $rowErrors[] = "Role must be Admin, Manager, or Employee.";
            }

            if (!in_array($status, ['active', 'inactive'])) {
                $status = 'active';
            }

            if (empty($deptName)) {
                $rowErrors[] = "Department is required.";
            }
 
            if (empty($desigName)) {
                $rowErrors[] = "Designation is required.";
            }
 
            if (empty($locName)) {
                $rowErrors[] = "Location is required.";
            }
 
            if (empty($shiftName)) {
                $rowErrors[] = "Shift is required.";
            }
 
            if (!empty($managerEmail) && !filter_var($managerEmail, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Manager Email format is invalid.";
            }

            if (empty($password)) {
                $password = 'Welcome@123';
            }

            if (!empty($dob)) {
                if (strtotime($dob)) {
                    $dob = date('Y-m-d', strtotime($dob));
                } else {
                    $rowErrors[] = "Date of Birth must be a valid date.";
                }
            } else {
                $dob = null;
            }

            // Check if there are errors for this row
            if (!empty($rowErrors)) {
                $errors[] = "Row {$rowNumber} (" . ($name ?: 'Unknown') . "): " . implode(' ', $rowErrors);
                continue;
            }

            // Database creation
            try {
                DB::transaction(function () use (
                    $name, $email, $password, $roleName, $status, $employeeCode, $joiningDate, 
                    $gender, $phone, $dob, $managerEmail, $bankName, $bankAccountNo, $panNo,
                    $deptName, $desigName, $locName, $shiftName, $data,
                    &$departmentsCache, &$designationsCache, &$locationsCache, &$shiftsCache
                ) {
                    // Check & create Department
                    $departmentId = $departmentsCache[$deptName] ?? null;
                    if ($departmentId) {
                        $dept = Department::withTrashed()->find($departmentId);
                        if ($dept && $dept->trashed()) {
                            $dept->restore();
                        }
                    } else {
                        $department = Department::create([
                            'department_name' => $data['department'],
                            'department_code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['department']), 0, 5)) . '-' . rand(100, 999),
                            'status' => 'active',
                        ]);
                        $departmentId = $department->id;
                        $departmentsCache[$deptName] = $departmentId;
                    }

                    // Check & create Designation
                    $designationId = $designationsCache[$desigName] ?? null;
                    if ($designationId) {
                        $desig = Designation::withTrashed()->find($designationId);
                        if ($desig && $desig->trashed()) {
                            $desig->restore();
                        }
                    } else {
                        $designation = Designation::create([
                            'designation_name' => $data['designation'],
                            'designation_code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['designation']), 0, 5)) . '-' . rand(100, 999),
                            'level' => 5,
                            'status' => 'active',
                        ]);
                        $designationId = $designation->id;
                        \App\Models\OrganizationalHierarchy::create([
                            'designation_id' => $designationId,
                            'parent_designation_id' => null,
                        ]);
                        $designationsCache[$desigName] = $designationId;
                    }

                    // Check & create Location
                    $locationId = $locationsCache[$locName] ?? null;
                    if ($locationId) {
                        $loc = Location::withTrashed()->find($locationId);
                        if ($loc && $loc->trashed()) {
                            $loc->restore();
                        }
                    } else {
                        $location = Location::create([
                            'location_name' => $data['location'],
                            'location_code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['location']), 0, 5)) . '-' . rand(100, 999),
                            'timezone' => 'America/New_York',
                            'status' => 'active',
                        ]);
                        $locationId = $location->id;
                        $locationsCache[$locName] = $locationId;
                    }

                    // Check & create Shift
                    $shiftId = $shiftsCache[$shiftName] ?? null;
                    if ($shiftId) {
                        $shf = Shift::withTrashed()->find($shiftId);
                        if ($shf && $shf->trashed()) {
                            $shf->restore();
                        }
                    } else {
                        $shift = Shift::create([
                            'shift_name' => $data['shift'],
                            'shift_code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['shift']), 0, 5)) . '-' . rand(100, 999),
                            'start_time' => '09:00',
                            'end_time' => '18:00',
                            'status' => 'active',
                        ]);
                        $shiftId = $shift->id;
                        $shiftsCache[$shiftName] = $shiftId;
                    }

                    // Resolve or create manager on-the-fly
                    $managerId = null;
                    if (!empty($managerEmail)) {
                        $manager = User::withTrashed()->where('email', $managerEmail)->first();
                        if ($manager) {
                            if ($manager->trashed()) {
                                $manager->restore();
                                $mgrDetail = $manager->employeeDetail()->withTrashed()->first();
                                if ($mgrDetail && $mgrDetail->trashed()) {
                                    $mgrDetail->restore();
                                }
                            }
                        } else {
                            $emailParts = explode('@', $managerEmail);
                            $username = $emailParts[0];
                            $nameParts = explode('.', $username);
                            $managerName = implode(' ', array_map('ucfirst', $nameParts));
                            
                            $manager = User::create([
                                'name' => $managerName,
                                'email' => $managerEmail,
                                'password' => Hash::make('Welcome@123'),
                                'status' => 'active',
                            ]);
                            $manager->assignRole('Manager');
                            
                            $managerDesignationId = $designationsCache['manager'] 
                                ?? $designationsCache['department manager'] 
                                ?? $designationId;

                            $manager->employeeDetail()->create([
                                'employee_code' => 'MGR-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $username), 0, 5)) . '-' . rand(100, 999),
                                'joining_date' => $joiningDate,
                                'location_id' => $locationId,
                                'department_id' => $departmentId,
                                'designation_id' => $managerDesignationId,
                                'shift_id' => $shiftId,
                                'gender' => 'other',
                            ]);
                        }
                        $managerId = $manager->id;
                    }

                    $user = User::withTrashed()->where('email', $email)->first();
                    if ($user) {
                        if ($user->trashed()) {
                            $user->restore();
                        }
                        $user->update([
                            'name' => $name,
                            'status' => $status,
                        ]);
                    } else {
                        $user = User::create([
                            'name' => $name,
                            'email' => $email,
                            'password' => Hash::make($password),
                            'status' => $status,
                        ]);
                    }

                    $user->syncRoles([$roleName]);

                    $detail = $user->employeeDetail()->withTrashed()->first();
                    if ($detail) {
                        if ($detail->trashed()) {
                            $detail->restore();
                        }
                        $detail->update([
                            'employee_code' => $employeeCode,
                            'joining_date' => $joiningDate,
                            'manager_id' => $managerId,
                            'location_id' => $locationId,
                            'department_id' => $departmentId,
                            'designation_id' => $designationId,
                            'shift_id' => $shiftId,
                            'bank_name' => $bankName,
                            'bank_account_no' => $bankAccountNo,
                            'pan_no' => $panNo,
                            'gender' => $gender,
                            'dob' => $dob,
                            'phone' => $phone,
                        ]);
                    } else {
                        $user->employeeDetail()->create([
                            'employee_code' => $employeeCode,
                            'joining_date' => $joiningDate,
                            'manager_id' => $managerId,
                            'location_id' => $locationId,
                            'department_id' => $departmentId,
                            'designation_id' => $designationId,
                            'shift_id' => $shiftId,
                            'bank_name' => $bankName,
                            'bank_account_no' => $bankAccountNo,
                            'pan_no' => $panNo,
                            'gender' => $gender,
                            'dob' => $dob,
                            'phone' => $phone,
                        ]);
                    }
                });
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber} ({$name}): Database error: " . $e->getMessage();
            }
        }

        fclose($handle);

        $flashMessage = "Successfully imported {$successCount} employee(s).";
        
        if (!empty($errors)) {
            return redirect()->route('employees.index')
                ->with('success', $flashMessage)
                ->with('import_errors', $errors);
        }

        return redirect()->route('employees.index')->with('success', $flashMessage);
    }

    public function bulkDestroy(Request $request)
    {
        if (Gate::denies('employees.delete')) {
            abort(403);
        }

        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No employee selected.'], 400);
        }

        if (in_array(auth()->id(), $ids)) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 400);
        }

        try {
            DB::transaction(function () use ($ids) {
                User::whereIn('id', $ids)->delete();
                EmployeeDetail::whereIn('user_id', $ids)->delete();
            });

            return response()->json(['success' => true, 'message' => 'Selected employees deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function bulkUpdate(Request $request)
    {
        if (Gate::denies('employees.edit')) {
            abort(403);
        }

        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No employee selected.'], 400);
        }

        try {
            DB::transaction(function () use ($ids, $request) {
                $userData = [];
                $detailData = [];

                if ($request->filled('status')) {
                    $userData['status'] = $request->input('status');
                }

                if ($request->filled('department_id')) {
                    $detailData['department_id'] = $request->input('department_id');
                }

                if ($request->filled('designation_id')) {
                    $detailData['designation_id'] = $request->input('designation_id');
                }

                if ($request->filled('location_id')) {
                    $detailData['location_id'] = $request->input('location_id');
                }

                if ($request->filled('shift_id')) {
                    $detailData['shift_id'] = $request->input('shift_id');
                }

                if (!empty($userData)) {
                    User::whereIn('id', $ids)->update($userData);
                }

                if (!empty($detailData)) {
                    EmployeeDetail::whereIn('user_id', $ids)->update($detailData);
                }

                if ($request->filled('role')) {
                    $roleName = $request->input('role');
                    $users = User::whereIn('id', $ids)->get();
                    foreach ($users as $user) {
                        $user->syncRoles([$roleName]);
                    }
                }
            });

            return response()->json(['success' => true, 'message' => 'Selected employees updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
