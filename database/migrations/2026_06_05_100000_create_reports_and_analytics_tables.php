<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('report_definitions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('report_name', 150);
            $table->string('report_code', 100)->unique();
            $table->foreignId('category_id')->constrained('report_categories')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->json('query_builder_config')->nullable();
            $table->json('default_columns');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('report_filters', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_definition_id')->constrained('report_definitions')->onDelete('cascade');
            $table->string('filter_key', 50);
            $table->string('filter_label', 100);
            $table->string('field_type', 50);
            $table->string('validation_rules', 255)->nullable();
            $table->string('default_value', 255)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['report_definition_id', 'filter_key']);
        });

        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_definition_id')->constrained('report_definitions')->onDelete('cascade');
            $table->string('template_name', 150);
            $table->boolean('is_custom')->default(true);
            $table->json('custom_columns')->nullable();
            $table->json('custom_filters')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_definition_id')->constrained('report_definitions')->onDelete('cascade');
            $table->foreignId('report_template_id')->nullable()->constrained('report_templates')->onDelete('set null');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->time('schedule_time');
            $table->string('recipient_email', 255);
            $table->enum('export_format', ['pdf', 'xlsx', 'csv'])->default('xlsx');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('last_run')->nullable();
            $table->timestamp('next_run')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'next_run']);
        });

        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_definition_id')->constrained('report_definitions')->onDelete('cascade');
            $table->foreignId('executed_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->enum('export_format', ['pdf', 'xlsx', 'csv']);
            $table->string('file_path', 255)->nullable();
            $table->bigInteger('file_size')->unsigned()->nullable();
            $table->text('error_message')->nullable();
            $table->json('parameters')->nullable();
            $table->timestamps();
        });

        Schema::create('report_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_definition_id')->constrained('report_definitions')->onDelete('cascade');
            $table->foreignId('executed_by')->constrained('users')->onDelete('cascade');
            $table->integer('execution_time')->unsigned(); // In milliseconds
            $table->enum('status', ['success', 'failed']);
            $table->string('file_path', 255)->nullable();
            $table->json('parameters')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('report_definition_id')->constrained('report_definitions')->onDelete('cascade');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('favorite_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('report_definition_id')->constrained('report_definitions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'report_definition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_reports');
        Schema::dropIfExists('saved_reports');
        Schema::dropIfExists('report_execution_logs');
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('report_templates');
        Schema::dropIfExists('report_filters');
        Schema::dropIfExists('report_definitions');
        Schema::dropIfExists('report_categories');
    }
};
