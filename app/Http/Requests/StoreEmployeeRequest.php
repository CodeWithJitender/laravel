<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('employees.create');
    }

    public function rules(): array
    {
        return [
            // User fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive'],
            'role' => ['required', 'in:Admin,Manager,Employee'],

            // EmployeeDetail fields
            'employee_code' => ['required', 'string', 'max:50', 'unique:employee_details,employee_code'],
            'joining_date' => ['required', 'date'],
            'exit_date' => ['nullable', 'date', 'after_or_equal:joining_date'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation_id' => ['required', 'exists:designations,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'pan_no' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'in:male,female,other'],
            'dob' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
