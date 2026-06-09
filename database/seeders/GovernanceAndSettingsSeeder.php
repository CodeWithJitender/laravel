<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanySetting;
use App\Models\SystemSetting;
use App\Models\EmailSetting;
use App\Models\NotificationSetting;
use App\Models\AttendanceSetting;
use App\Models\LeaveSetting;
use App\Models\PayrollSetting;
use App\Models\SecuritySetting;
use App\Models\FileStorageSetting;
use App\Models\BackupSetting;
use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;

class GovernanceAndSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Company Settings
        CompanySetting::create([
            'company_name' => 'HRMS Enterprise Corp',
            'company_code' => 'HEC',
            'website' => 'https://hrms.enterprise.corp',
            'email' => 'contact@enterprise.corp',
            'phone' => '+1 (555) 019-2834',
            'tax_number' => 'TX-998234-A',
            'registration_number' => 'REG-12009238',
            'address' => '100 Corporate Parkway',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'postal_code' => '10001',
        ]);

        // 2. Seed System Settings
        SystemSetting::create([
            'app_name' => 'HRMS',
            'app_version' => '1.0.0',
            'default_timezone' => 'UTC',
            'default_currency' => 'USD',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'language' => 'en',
            'system_status' => 'online',
        ]);

        // 3. Seed Email Settings
        EmailSetting::create([
            'smtp_host' => 'smtp.mailtrap.io',
            'smtp_port' => 2525,
            'smtp_username' => 'test-user',
            'smtp_password' => 'test-password',
            'encryption' => 'tls',
            'sender_name' => 'HR Portal Admin',
            'sender_email' => 'admin@company.com',
        ]);

        // 4. Seed Notification Settings
        NotificationSetting::create([
            'in_app_enabled' => true,
            'email_enabled' => true,
            'sms_enabled' => false,
            'push_enabled' => false,
        ]);

        // 5. Seed Attendance Settings
        AttendanceSetting::create([
            'grace_period_minutes' => 15,
            'minimum_working_hours' => 8.00,
            'half_day_working_hours' => 4.00,
            'overtime_multiplier' => 1.50,
        ]);

        // 6. Seed Leave Settings
        LeaveSetting::create([
            'accrual_cycle' => 'monthly',
            'carry_forward_enabled' => true,
            'max_accumulated_days' => 30,
        ]);

        // 7. Seed Payroll Settings
        PayrollSetting::create([
            'payroll_cycle' => 'monthly',
            'processing_day' => 25,
            'pf_percentage' => 12.00,
            'professional_tax_threshold' => 15000.00,
        ]);

        // 8. Seed Security Settings
        SecuritySetting::create([
            'min_password_length' => 8,
            'password_expiry_days' => 90,
            'failed_login_attempts' => 5,
            'account_lock_minutes' => 15,
            'session_timeout_minutes' => 120,
        ]);

        // 9. Seed File Storage Settings
        FileStorageSetting::create([
            'default_disk' => 'local',
        ]);

        // 10. Seed Backup Settings
        BackupSetting::create([
            'backup_frequency' => 'daily',
            'backup_time' => '02:00',
            'include_files' => false,
            'retention_days' => 30,
        ]);

        // 11. Seed Feature Flags
        FeatureFlag::create([
            'flag_key' => 'payroll_module_enabled',
            'flag_value' => true,
            'description' => 'Toggles active salary calculations, payslip views and processing.',
        ]);
        FeatureFlag::create([
            'flag_key' => 'two_factor_auth_enabled',
            'flag_value' => false,
            'description' => 'Mandatory multi-factor authentication check for platform logins.',
        ]);

        // Flush settings cache keys to make sure seeded values load instantly
        foreach (['company', 'system', 'email', 'notification', 'attendance', 'leave', 'payroll', 'security', 'storage', 'backup'] as $group) {
            Cache::forget("settings.{$group}");
        }
        Cache::forget("feature_flag.payroll_module_enabled");
        Cache::forget("feature_flag.two_factor_auth_enabled");

        // 12. Seed Governance Permissions
        $this->seedGovernancePermissions();
    }

    protected function seedGovernancePermissions(): void
    {
        $permissions = [
            'audit.view',
            'activity.view',
            'company_settings.manage',
            'system_settings.manage',
            'security_settings.manage',
            'feature_flags.manage',
        ];

        foreach ($permissions as $permName) {
            \Spatie\Permission\Models\Permission::findOrCreate($permName, 'web');
        }

        // Give permissions to Admin
        $adminRole = \Spatie\Permission\Models\Role::findOrCreate('Admin', 'web');
        $adminRole->givePermissionTo($permissions);
    }
}
