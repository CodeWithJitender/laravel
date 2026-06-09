<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClockPunchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('attendance.create');
    }

    public function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'device_info' => ['nullable', 'string'],
            'method' => ['nullable', 'string', 'in:web,mobile,biometric,api'],
        ];
    }
}
