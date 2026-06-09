<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('department.edit');
    }

    public function rules(): array
    {
        $department = $this->route('department');
        $departmentId = $department instanceof \Illuminate\Database\Eloquent\Model ? $department->id : $department;

        return [
            'department_code' => ['required', 'string', 'max:50', 'unique:departments,department_code,' . $departmentId],
            'department_name' => ['required', 'string', 'max:150', 'unique:departments,department_name,' . $departmentId],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'head_employee_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
