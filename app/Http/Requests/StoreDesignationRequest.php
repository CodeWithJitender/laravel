<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('designation.create');
    }

    public function rules(): array
    {
        return [
            'designation_code' => ['required', 'string', 'max:50', 'unique:designations,designation_code'],
            'designation_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'level' => ['required', 'integer', 'min:1', 'max:5'],
            'status' => ['required', 'in:active,inactive'],
            'parent_designation_id' => ['nullable', 'integer', 'exists:designations,id'],
        ];
    }
}
