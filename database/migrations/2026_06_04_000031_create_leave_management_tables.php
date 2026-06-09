<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Leave Types
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('#6366f1');
            $table->boolean('is_paid')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Leave Policies
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->decimal('annual_allocation', 5, 2);
            $table->boolean('monthly_accrual')->default(false);
            $table->decimal('carry_forward_limit', 5, 2)->default(0.00);
            $table->integer('max_consecutive_days')->nullable();
            $table->integer('notice_period_days')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 3. Leave Policy Rules
        Schema::create('leave_policy_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('leave_policies')->cascadeOnDelete();
            $table->string('rule_type'); // gender, department, location, employment_type
            $table->string('rule_operator'); // in, not_in
            $table->json('rule_values'); // array of target values
            $table->timestamps();
        });

        // 4. Leave Balances
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->decimal('opening_balance', 5, 2)->default(0.00);
            $table->decimal('allocated_balance', 5, 2)->default(0.00);
            $table->decimal('accrued_balance', 5, 2)->default(0.00);
            $table->decimal('used_balance', 5, 2)->default(0.00);
            $table->decimal('pending_balance', 5, 2)->default(0.00);
            $table->decimal('carry_forward_balance', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id']);
        });

        // 5. Leave Requests
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 4, 2);
            $table->boolean('half_day')->default(false);
            $table->enum('half_day_session', ['first_half', 'second_half'])->nullable();
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->string('emergency_phone');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'withdrawn', 'partially_approved'])->default('pending');
            $table->timestamp('applied_at')->useCurrent();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Leave Request Days
        Schema::create('leave_request_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->date('leave_date');
            $table->decimal('day_weight', 2, 1)->default(1.0);
            $table->string('session')->default('full'); // full, first_half, second_half
        });

        // 7. Leave Approvals
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users');
            $table->integer('level')->default(1);
            $table->enum('status', ['approved', 'rejected'])->default('approved');
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 8. Leave Status History
        Schema::create('leave_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users'); // actor
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 9. Leave Accruals
        Schema::create('leave_accruals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->decimal('accrued_amount', 5, 2);
            $table->date('run_date');
            $table->timestamp('created_at')->useCurrent();
        });

        // 10. Leave Carry Forwards
        Schema::create('leave_carry_forwards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types');
            $table->decimal('amount_carried', 5, 2);
            $table->decimal('amount_expired', 5, 2);
            $table->smallInteger('run_year');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_carry_forwards');
        Schema::dropIfExists('leave_accruals');
        Schema::dropIfExists('leave_status_history');
        Schema::dropIfExists('leave_approvals');
        Schema::dropIfExists('leave_request_days');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_policy_rules');
        Schema::dropIfExists('leave_policies');
        Schema::dropIfExists('leave_types');
    }
};
