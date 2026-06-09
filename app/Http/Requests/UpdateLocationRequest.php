<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('location.edit');
    }

    public function rules(): array
    {
        $location = $this->route('location');
        $locationId = $location instanceof \Illuminate\Database\Eloquent\Model ? $location->id : $location;

        return [
            'location_code' => ['required', 'string', 'max:50', 'unique:locations,location_code,' . $locationId],
            'location_name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'timezone' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
