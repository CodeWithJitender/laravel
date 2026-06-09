<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('leave.approve');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:approved,rejected'],
            'remarks' => ['required_if:status,rejected', 'nullable', 'string', 'min:5', 'max:1000'],
        ];
    }
}
