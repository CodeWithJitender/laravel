<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('shift.create');
    }

    public function rules(): array
    {
        return [
            'shift_code' => ['required', 'string', 'max:50', 'unique:shifts,shift_code'],
            'shift_name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'grace_period_minutes' => ['required', 'integer', 'min:0'],
            'break_minutes' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
