<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeTimingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('office_timing.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'working_days' => ['required', 'array'],
            'working_days.*' => ['string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'minimum_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'half_day_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'weekly_off' => ['required', 'array'],
            'weekly_off.*' => ['string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
