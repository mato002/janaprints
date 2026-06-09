<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_payslips') && Schema::hasTable('payroll_payslip_items')) {
            return;
        }

        if (! Schema::hasTable('payroll_payslips')) {
            Schema::create('payroll_payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 30)->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('total_allowances', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('paye', 12, 2)->default(0);
            $table->decimal('shif', 12, 2)->default(0);
            $table->decimal('nssf', 12, 2)->default(0);
            $table->decimal('housing_levy', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->unsignedSmallInteger('days_worked')->default(0);
            $table->unsignedSmallInteger('leave_days')->default(0);
            $table->unsignedSmallInteger('absent_days')->default(0);
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
            });
        }

        if (! Schema::hasTable('payroll_payslip_items')) {
            Schema::create('payroll_payslip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_payslip_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 20);
            $table->string('code', 30);
            $table->string('name');
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_payslip_items');
        Schema::dropIfExists('payroll_payslips');
    }
};
