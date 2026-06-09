<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('generate', \App\Models\ReportDefinition::class);
    }

    public function rules(): array
    {
        return [
            'report_code' => 'required|string|exists:report_definitions,report_code',
            'filters' => 'nullable|array',
            'filters.start_date' => 'nullable|date',
            'filters.end_date' => 'nullable|date|after_or_equal:filters.start_date',
            'filters.department_id' => 'nullable|exists:departments,id',
            'filters.location_id' => 'nullable|exists:locations,id',
            'filters.designation_id' => 'nullable|exists:designations,id',
            'filters.manager_id' => 'nullable|exists:users,id',
            'filters.status' => 'nullable|string',
            'export_format' => 'required|in:pdf,xlsx,csv',
        ];
    }
}
