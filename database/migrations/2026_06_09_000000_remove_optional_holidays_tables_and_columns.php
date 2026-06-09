<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('optional_holiday_requests');
        Schema::dropIfExists('optional_holiday_balances');
        
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn('is_optional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->boolean('is_optional')->default(false)->after('holiday_type_id');
        });

        Schema::create('optional_holiday_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('allocated_quota', 4, 2)->default(2.00);
            $table->decimal('used_quota', 4, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['employee_id', 'year']);
        });

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
    }
};
