<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $holidayId = $this->route('holiday') ?: $this->route('id');

        return [
            'holiday_name' => 'required|string|max:255',
            'holiday_code' => [
                'required',
                'string',
                'max:50',
                $holidayId ? Rule::unique('holidays', 'holiday_code')->ignore($holidayId) : Rule::unique('holidays', 'holiday_code'),
            ],
            'holiday_date' => 'required|date',
            'holiday_type_id' => 'required|exists:holiday_types,id',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',

            'is_paid' => 'nullable|boolean',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published,archived,cancelled',
        ];
    }
}
