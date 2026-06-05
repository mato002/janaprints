<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_exits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40);
            $table->string('exit_type', 30);
            $table->string('status', 30)->default('initiated');
            $table->date('last_working_date');
            $table->date('exit_date');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('leave_balance_days', 8, 2)->default(0);
            $table->decimal('leave_balance_amount', 14, 2)->default(0);
            $table->decimal('salary_balance', 14, 2)->default(0);
            $table->decimal('deductions_total', 14, 2)->default(0);
            $table->decimal('net_final_dues', 14, 2)->default(0);
            $table->foreignId('initiated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('initiated_at')->nullable();
            $table->foreignId('settled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('settled_at')->nullable();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'reference'], 'emp_exit_ref_unique');
            $table->index(['company_id', 'employee_id'], 'emp_exit_emp_idx');
            $table->index(['company_id', 'status'], 'emp_exit_status_idx');
        });

        Schema::create('employee_exit_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_exit_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30);
            $table->string('status', 20)->default('pending');
            $table->foreignId('cleared_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cleared_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_exit_id', 'category'], 'emp_exit_clear_cat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_exit_clearances');
        Schema::dropIfExists('employee_exits');
    }
};
