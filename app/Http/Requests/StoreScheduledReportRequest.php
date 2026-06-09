<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduledReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('schedule', \App\Models\ReportDefinition::class);
    }

    public function rules(): array
    {
        return [
            'report_definition_id' => 'required|exists:report_definitions,id',
            'report_template_id' => 'nullable|exists:report_templates,id',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'schedule_time' => 'required|date_format:H:i',
            'recipient_email' => 'required|string|max:255',
            'export_format' => 'required|in:pdf,xlsx,csv',
        ];
    }
}
