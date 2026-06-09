<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('office_timings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->json('working_days');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('minimum_hours', 4, 2)->default(8.00);
            $table->decimal('half_day_hours', 4, 2)->default(4.00);
            $table->json('weekly_off');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('department_heads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
        });

        Schema::create('organizational_hierarchy', function (Blueprint $table) {
            $table->id();
            $table->foreignId('designation_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_designation_id')->nullable()->constrained('designations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizational_hierarchy');
        Schema::dropIfExists('department_heads');
        Schema::dropIfExists('office_timings');
    }
};
