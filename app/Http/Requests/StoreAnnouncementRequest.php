<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['Admin', 'Manager']);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'exists:announcement_categories,id'],
            'audience_type' => ['required', 'in:all,department,location,role,individual'],
            'audience_values' => ['nullable', 'array'],
            'audience_values.*' => ['string'],
            'publish_at' => ['nullable', 'date'],
            'expire_at' => ['nullable', 'date', 'after_or_equal:publish_at'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
}
