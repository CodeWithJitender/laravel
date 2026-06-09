<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('attendance.correction.request');
    }

    public function rules(): array
    {
        return [
            'requested_date' => ['required', 'date', 'before_or_equal:today'],
            'requested_clock_in' => ['required', 'date_format:H:i'],
            'requested_clock_out' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }
}
