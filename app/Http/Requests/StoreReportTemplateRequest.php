<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageTemplates', \App\Models\ReportDefinition::class);
    }

    public function rules(): array
    {
        return [
            'report_definition_id' => 'required|exists:report_definitions,id',
            'template_name' => 'required|string|max:150',
            'custom_columns' => 'nullable|array',
            'custom_filters' => 'nullable|array',
        ];
    }
}
