<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Announcement Categories
        Schema::create('announcement_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->default('#6366f1');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. Announcements
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content');
            $table->foreignId('category_id')->constrained('announcement_categories')->cascadeOnDelete();
            $table->enum('audience_type', ['all', 'department', 'location', 'role', 'individual'])->default('all');
            $table->json('audience_values')->nullable(); // Target IDs or roles
            $table->enum('status', ['draft', 'scheduled', 'published', 'archived', 'expired'])->default('draft');
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 3. Announcement Recipients (Read tracking)
        Schema::create('announcement_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['announcement_id', 'employee_id']);
        });

        // 4. Notification Templates
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key')->unique(); // e.g. leave_approved, employee_created
            $table->string('name');
            $table->string('subject');
            $table->text('content');
            $table->json('channels'); // default channels: ['in_app', 'email']
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 5. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('subject');
            $table->text('message');
            $table->string('type'); // system, HR, attendance, leave, payroll, holiday, announcement, security, custom
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('channel')->nullable(); // selected channels e.g. "in_app,email" or JSON
            $table->enum('status', ['draft', 'queued', 'sent', 'failed'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Notification Recipients
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['sent', 'delivered', 'read', 'failed', 'archived'])->default('sent');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->unique(['notification_id', 'employee_id']);
        });

        // 7. Notification Channels
        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // in_app, email, push, sms, whatsapp
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 8. Notification Events (Registry mapping event classes to templates)
        Schema::create('notification_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_class')->unique(); // e.g. App\Events\LeaveApproved
            $table->foreignId('template_id')->constrained('notification_templates')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 9. Notification Delivery Logs
        Schema::create('notification_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('channel'); // email, in_app, push, sms
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error_message')->nullable();
            $table->string('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_delivery_logs');
        Schema::dropIfExists('notification_events');
        Schema::dropIfExists('notification_channels');
        Schema::dropIfExists('notification_recipients');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('announcement_recipients');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('announcement_categories');
    }
};
