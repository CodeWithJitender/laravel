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
        // 1. Salary Components
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('component_name');
            $table->string('component_code')->unique();
            $table->enum('component_type', ['earning', 'deduction']);
            $table->enum('calculation_type', ['fixed', 'percentage_of_basic', 'percentage_of_gross', 'custom_formula']);
            $table->decimal('default_value', 10, 2)->default(0.00);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. Salary Structures
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 3. Salary Structure Components (Many-to-Many Pivot)
        Schema::create('salary_structure_components', function (Blueprint $table) {
            $table->foreignId('salary_structure_id')->constrained('salary_structures')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->decimal('calculation_value', 10, 2)->default(0.00);
            $table->string('calculation_formula')->nullable();
            $table->integer('sort_order')->default(0);
            $table->primary(['salary_structure_id', 'salary_component_id'], 'ssc_primary');
        });

        // 4. Employee Salary Structures
        Schema::create('employee_salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('salary_structure_id')->constrained('salary_structures')->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('monthly_gross_salary', 15, 2);
            $table->decimal('annual_ctc', 15, 2);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 5. Salary Revisions
        Schema::create('salary_revisions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('old_gross_salary', 15, 2);
            $table->decimal('new_gross_salary', 15, 2);
            $table->decimal('old_annual_ctc', 15, 2);
            $table->decimal('new_annual_ctc', 15, 2);
            $table->date('revision_date');
            $table->date('effective_date');
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Payroll Runs
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->integer('run_month');
            $table->integer('run_year');
            $table->enum('run_type', ['monthly', 'off_cycle', 'bonus', 'adjustment'])->default('monthly');
            $table->enum('status', ['draft', 'processing', 'calculated', 'approved', 'published', 'cancelled'])->default('draft');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->integer('total_employees')->default(0);
            $table->decimal('total_gross', 15, 2)->default(0.00);
            $table->decimal('total_earnings', 15, 2)->default(0.00);
            $table->decimal('total_deductions', 15, 2)->default(0.00);
            $table->decimal('total_net', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 7. Payroll Run Employees
        Schema::create('payroll_run_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('salary_structure_id')->constrained('salary_structures');
            $table->decimal('monthly_gross_salary', 15, 2);
            $table->integer('total_working_days');
            $table->decimal('paid_days', 4, 2);
            $table->decimal('lop_days', 4, 2);
            $table->decimal('gross_salary_earned', 15, 2);
            $table->decimal('total_earnings', 15, 2);
            $table->decimal('total_deductions', 15, 2);
            $table->decimal('net_salary', 15, 2);
            $table->enum('status', ['draft', 'processing', 'calculated', 'approved', 'published', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
        });

        // 8. Payroll Items
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_employee_id')->constrained('payroll_run_employees')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->string('component_name');
            $table->string('component_code');
            $table->enum('component_type', ['earning', 'deduction']);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

        // 9. Payroll Adjustments
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_employee_id')->constrained('payroll_run_employees')->cascadeOnDelete();
            $table->enum('type', ['earning', 'deduction']);
            $table->decimal('amount', 15, 2);
            $table->string('reason');
            $table->timestamps();
        });

        // 10. Payroll Approvals
        Schema::create('payroll_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('approval_level'); // 'Finance', 'HR', 'Management'
            $table->enum('status', ['approved', 'rejected']);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 11. Payslips
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('payroll_run_employee_id')->constrained('payroll_run_employees')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference_no')->unique();
            $table->decimal('gross_salary', 15, 2);
            $table->decimal('total_earnings', 15, 2);
            $table->decimal('total_deductions', 15, 2);
            $table->decimal('net_salary', 15, 2);
            $table->string('pdf_path')->nullable();
            $table->string('secure_hash')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // 12. Employee Loans
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('remaining_principal', 15, 2);
            $table->decimal('monthly_emi', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(0.00);
            $table->date('disbursal_date');
            $table->enum('status', ['pending', 'active', 'repaid', 'defaulted'])->default('pending');
            $table->timestamps();
        });

        // 13. Loan Repayments
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained('employee_loans')->cascadeOnDelete();
            $table->foreignId('payroll_run_employee_id')->nullable()->constrained('payroll_run_employees')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('repayment_date');
            $table->string('payment_method')->default('payroll'); // 'payroll', 'manual'
            $table->timestamps();
        });

        // 14. Salary Advances
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('request_date');
            $table->integer('recovery_month');
            $table->integer('recovery_year');
            $table->enum('status', ['pending', 'approved', 'recovered', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advances');
        Schema::dropIfExists('loan_repayments');
        Schema::dropIfExists('employee_loans');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_approvals');
        Schema::dropIfExists('payroll_adjustments');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_run_employees');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('salary_revisions');
        Schema::dropIfExists('employee_salary_structures');
        Schema::dropIfExists('salary_structure_components');
        Schema::dropIfExists('salary_structures');
        Schema::dropIfExists('salary_components');
    }
};
