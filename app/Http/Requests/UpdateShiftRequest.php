<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('shift.edit');
    }

    public function rules(): array
    {
        $shift = $this->route('shift');
        $shiftId = $shift instanceof \Illuminate\Database\Eloquent\Model ? $shift->id : $shift;

        return [
            'shift_code' => ['required', 'string', 'max:50', 'unique:shifts,shift_code,' . $shiftId],
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
