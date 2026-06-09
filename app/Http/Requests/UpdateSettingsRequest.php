<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('Admin');
    }

    public function rules(): array
    {
        $group = $this->route('group');

        switch ($group) {
            case 'company':
                return [
                    'company_name' => 'required|string|max:150',
                    'company_code' => 'required|string|max:50',
                    'company_logo' => 'nullable|string|max:255',
                    'address' => 'nullable|string|max:255',
                    'city' => 'nullable|string|max:100',
                    'state' => 'nullable|string|max:100',
                    'country' => 'nullable|string|max:100',
                    'postal_code' => 'nullable|string|max:20',
                    'website' => 'nullable|url|max:150',
                    'email' => 'nullable|email|max:150',
                    'phone' => 'nullable|string|max:50',
                    'tax_number' => 'nullable|string|max:50',
                    'registration_number' => 'nullable|string|max:50',
                ];
            case 'system':
                return [
                    'app_name' => 'required|string|max:100',
                    'app_version' => 'required|string|max:20',
                    'default_timezone' => 'required|string|timezone',
                    'default_currency' => 'required|string|max:10',
                    'date_format' => 'required|string|max:20',
                    'time_format' => 'required|string|max:20',
                    'language' => 'required|string|max:10',
                    'system_status' => 'required|in:online,maintenance',
                ];
            case 'email':
                return [
                    'smtp_host' => 'required|string|max:150',
                    'smtp_port' => 'required|integer',
                    'smtp_username' => 'nullable|string|max:150',
                    'smtp_password' => 'nullable|string|max:255',
                    'encryption' => 'nullable|string|max:20',
                    'sender_name' => 'required|string|max:100',
                    'sender_email' => 'required|email|max:150',
                ];
            case 'notification':
                return [
                    'in_app_enabled' => 'required|boolean',
                    'email_enabled' => 'required|boolean',
                    'sms_enabled' => 'required|boolean',
                    'push_enabled' => 'required|boolean',
                ];
            case 'attendance':
                return [
                    'default_shift_id' => 'nullable|exists:shifts,id',
                    'grace_period_minutes' => 'required|integer|min:0',
                    'minimum_working_hours' => 'required|numeric|min:0|max:24',
                    'half_day_working_hours' => 'required|numeric|min:0|max:24',
                    'overtime_multiplier' => 'required|numeric|min:1|max:5',
                ];
            case 'leave':
                return [
                    'accrual_cycle' => 'required|in:monthly,yearly',
                    'carry_forward_enabled' => 'required|boolean',
                    'max_accumulated_days' => 'required|integer|min:0',
                ];
            case 'payroll':
                return [
                    'payroll_cycle' => 'required|in:monthly,weekly,bi-weekly',
                    'processing_day' => 'required|integer|min:1|max:31',
                    'pf_percentage' => 'required|numeric|min:0|max:100',
                    'professional_tax_threshold' => 'required|numeric|min:0',
                ];
            case 'security':
                return [
                    'min_password_length' => 'required|integer|min:6|max:32',
                    'password_expiry_days' => 'required|integer|min:0',
                    'failed_login_attempts' => 'required|integer|min:0|max:20',
                    'account_lock_minutes' => 'required|integer|min:0',
                    'session_timeout_minutes' => 'required|integer|min:1',
                ];
            case 'storage':
                return [
                    'default_disk' => 'required|in:local,s3,gcs',
                    's3_key' => 'nullable|string|max:255',
                    's3_secret' => 'nullable|string|max:255',
                    's3_region' => 'nullable|string|max:100',
                    's3_bucket' => 'nullable|string|max:150',
                ];
            case 'backup':
                return [
                    'backup_frequency' => 'required|in:daily,weekly,monthly',
                    'backup_time' => 'required|date_format:H:i',
                    'include_files' => 'required|boolean',
                    'retention_days' => 'required|integer|min:1',
                ];
            default:
                return [];
        }
    }
}
