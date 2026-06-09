<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('module', 100);
            $table->string('action', 50);
            $table->string('record_type', 100);
            $table->bigInteger('record_id')->unsigned();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('browser', 150)->nullable();
            $table->string('device', 100)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['record_type', 'record_id']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('activity', 100);
            $table->string('module', 100);
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('user_login_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['success', 'failed']);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 150);
            $table->string('company_code', 50)->unique();
            $table->string('company_logo', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('website', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->string('registration_number', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name', 100)->default('HRMS');
            $table->string('app_version', 20)->default('1.0.0');
            $table->string('default_timezone', 100)->default('UTC');
            $table->string('default_currency', 10)->default('USD');
            $table->string('date_format', 20)->default('Y-m-d');
            $table->string('time_format', 20)->default('H:i:s');
            $table->string('language', 10)->default('en');
            $table->enum('system_status', ['online', 'maintenance'])->default('online');
            $table->timestamps();
        });

        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('smtp_host', 150);
            $table->integer('smtp_port');
            $table->string('smtp_username', 150)->nullable();
            $table->string('smtp_password', 255)->nullable();
            $table->string('encryption', 20)->nullable()->default('tls');
            $table->string('sender_name', 100);
            $table->string('sender_email', 150);
            $table->timestamps();
        });

        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('push_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('default_shift_id')->unsigned()->nullable();
            $table->integer('grace_period_minutes')->default(15);
            $table->decimal('minimum_working_hours', 4, 2)->default(8.00);
            $table->decimal('half_day_working_hours', 4, 2)->default(4.00);
            $table->decimal('overtime_multiplier', 3, 2)->default(1.50);
            $table->timestamps();
        });

        Schema::create('leave_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('accrual_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->boolean('carry_forward_enabled')->default(true);
            $table->integer('max_accumulated_days')->default(30);
            $table->timestamps();
        });

        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('payroll_cycle', ['monthly', 'weekly', 'bi-weekly'])->default('monthly');
            $table->integer('processing_day')->default(25);
            $table->decimal('pf_percentage', 4, 2)->default(12.00);
            $table->decimal('professional_tax_threshold', 10, 2)->default(15000.00);
            $table->timestamps();
        });

        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('min_password_length')->default(8);
            $table->integer('password_expiry_days')->default(90);
            $table->integer('failed_login_attempts')->default(5);
            $table->integer('account_lock_minutes')->default(15);
            $table->integer('session_timeout_minutes')->default(120);
            $table->timestamps();
        });

        Schema::create('file_storage_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('default_disk', ['local', 's3', 'gcs'])->default('local');
            $table->string('s3_key', 255)->nullable();
            $table->string('s3_secret', 255)->nullable();
            $table->string('s3_region', 100)->nullable();
            $table->string('s3_bucket', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('backup_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('backup_frequency', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->time('backup_time')->default('02:00');
            $table->boolean('include_files')->default(false);
            $table->integer('retention_days')->default(30);
            $table->timestamps();
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('flag_key', 100)->unique();
            $table->boolean('flag_value')->default(true);
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('backup_settings');
        Schema::dropIfExists('file_storage_settings');
        Schema::dropIfExists('security_settings');
        Schema::dropIfExists('payroll_settings');
        Schema::dropIfExists('leave_settings');
        Schema::dropIfExists('attendance_settings');
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('email_settings');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('user_login_history');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('audit_logs');
    }
};
