<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date')->index();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            $table->decimal('worked_hours', 4, 2)->default(0.00);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_exit_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->enum('attendance_status', [
                'Present', 'Absent', 'Half Day', 'Late', 'Work From Home', 
                'Holiday', 'Weekly Off', 'On Leave', 'Missed Punch'
            ])->default('Absent');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'attendance_date']);
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['clock_in', 'clock_out']);
            $table->dateTime('log_time');
            $table->string('ip_address', 45)->nullable();
            $table->text('device_info')->nullable();
            $table->enum('method', ['web', 'mobile', 'biometric', 'api'])->default('web');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->date('requested_date');
            $table->dateTime('requested_clock_in');
            $table->dateTime('requested_clock_out');
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('month');
            $table->smallInteger('year');
            $table->tinyInteger('present_days')->default(0);
            $table->tinyInteger('absent_days')->default(0);
            $table->tinyInteger('late_days')->default(0);
            $table->tinyInteger('leave_days')->default(0);
            $table->tinyInteger('holiday_days')->default(0);
            $table->tinyInteger('wfh_days')->default(0);
            $table->tinyInteger('missed_punch_days')->default(0);
            $table->decimal('total_working_hours', 6, 2)->default(0.00);
            $table->decimal('total_overtime_hours', 6, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_monthly_summaries');
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('attendances');
    }
};
