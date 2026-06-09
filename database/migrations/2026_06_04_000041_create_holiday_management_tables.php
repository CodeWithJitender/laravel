<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Holiday Types
        Schema::create('holiday_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. Holidays
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('holiday_name');
            $table->string('holiday_code')->unique();
            $table->text('description')->nullable();
            $table->date('holiday_date');
            $table->foreignId('holiday_type_id')->constrained('holiday_types')->cascadeOnDelete();
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_paid')->default(true);
            $table->enum('status', ['draft', 'published', 'archived', 'cancelled'])->default('draft');
            $table->timestamp('publish_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Holiday Locations (Many-to-Many)
        Schema::create('holiday_locations', function (Blueprint $table) {
            $table->foreignId('holiday_id')->constrained('holidays')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->primary(['holiday_id', 'location_id']);
        });

        // 4. Optional Holiday Balances
        Schema::create('optional_holiday_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('allocated_quota', 4, 2)->default(2.00);
            $table->decimal('used_quota', 4, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['employee_id', 'year']);
        });

        // 5. Optional Holiday Requests
        Schema::create('optional_holiday_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('holiday_id')->constrained('holidays')->cascadeOnDelete();
            $table->date('request_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'holiday_id']);
        });

        // 6. Holiday Reminders
        Schema::create('holiday_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holiday_id')->constrained('holidays')->cascadeOnDelete();
            $table->integer('reminder_days_before');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // 7. Holiday Notifications (Links holidays to the notification system)
        Schema::create('holiday_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holiday_id')->constrained('holidays')->cascadeOnDelete();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_notifications');
        Schema::dropIfExists('holiday_reminders');
        Schema::dropIfExists('optional_holiday_requests');
        Schema::dropIfExists('optional_holiday_balances');
        Schema::dropIfExists('holiday_locations');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('holiday_types');
    }
};
