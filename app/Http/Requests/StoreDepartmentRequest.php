<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('department.create');
    }

    public function rules(): array
    {
        return [
            'department_code' => ['required', 'string', 'max:50', 'unique:departments,department_code'],
            'department_name' => ['required', 'string', 'max:150', 'unique:departments,department_name'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'head_employee_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
