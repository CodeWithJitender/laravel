<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('designation.edit');
    }

    public function rules(): array
    {
        $designation = $this->route('designation');
        $designationId = $designation instanceof \Illuminate\Database\Eloquent\Model ? $designation->id : $designation;

        return [
            'designation_code' => ['required', 'string', 'max:50', 'unique:designations,designation_code,' . $designationId],
            'designation_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'level' => ['required', 'integer', 'min:1', 'max:5'],
            'status' => ['required', 'in:active,inactive'],
            'parent_designation_id' => [
                'nullable',
                'integer',
                'exists:designations,id',
                // Prevent assigning itself as parent
                function ($attribute, $value, $fail) use ($designationId) {
                    if ($value == $designationId) {
                        $fail('A designation cannot be its own reporting parent.');
                    }
                }
            ],
        ];
    }
}
