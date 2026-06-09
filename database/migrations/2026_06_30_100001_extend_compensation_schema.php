<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compensation_salary_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('house_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('medical_allowance', 12, 2)->default(0);
            $table->decimal('risk_allowance', 12, 2)->default(0);
            $table->decimal('responsibility_allowance', 12, 2)->default(0);
            $table->string('payment_frequency', 20)->default('monthly');
            $table->string('payroll_group', 30)->default('main');
            $table->string('currency', 3)->default('KES');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('compensation_allowance_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->string('calculation_type', 20)->default('fixed');
            $table->string('frequency', 20)->default('recurring');
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->decimal('percentage_rate', 8, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('compensation_deduction_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->string('category', 30)->default('custom');
            $table->string('calculation_type', 20)->default('fixed');
            $table->string('frequency', 20)->default('recurring');
            $table->decimal('default_amount', 12, 2)->default(0);
            $table->decimal('percentage_rate', 8, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::table('employee_compensations', function (Blueprint $table) {
            $table->decimal('risk_allowance', 12, 2)->default(0)->after('medical_allowance');
            $table->decimal('responsibility_allowance', 12, 2)->default(0)->after('risk_allowance');
            $table->string('payment_frequency', 20)->default('monthly')->after('effective_from');
            $table->string('payroll_group', 30)->default('main')->after('payment_frequency');
            $table->string('currency', 3)->default('KES')->after('payroll_group');
            $table->string('status', 20)->default('active')->after('currency');
            $table->text('change_reason')->nullable()->after('status');
            $table->foreignId('changed_by_user_id')->nullable()->after('change_reason')->constrained('users')->nullOnDelete();
            $table->foreignId('salary_template_id')->nullable()->after('changed_by_user_id')->constrained('compensation_salary_templates')->nullOnDelete();

            $table->index(['company_id', 'status']);
            $table->index(['employee_id', 'effective_from']);
        });

        Schema::create('compensation_salary_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_compensation_id')->constrained('employee_compensations')->cascadeOnDelete();
            $table->decimal('old_salary', 12, 2)->default(0);
            $table->decimal('new_salary', 12, 2)->default(0);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->date('effective_from');
            $table->timestamps();

            $table->index(['employee_id', 'effective_from']);
        });

        Schema::table('payroll_allowances', function (Blueprint $table) {
            $table->string('calculation_type', 20)->default('fixed')->after('name');
            $table->string('frequency', 20)->default('recurring')->after('calculation_type');
            $table->decimal('percentage_rate', 8, 4)->nullable()->after('amount');
            $table->foreignId('allowance_definition_id')->nullable()->after('percentage_rate')
                ->constrained('compensation_allowance_definitions')->nullOnDelete();
            $table->timestamp('applied_at')->nullable()->after('is_active');
        });

        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->string('calculation_type', 20)->default('fixed')->after('name');
            $table->string('frequency', 20)->default('recurring')->after('calculation_type');
            $table->decimal('percentage_rate', 8, 4)->nullable()->after('amount');
            $table->foreignId('deduction_definition_id')->nullable()->after('percentage_rate')
                ->constrained('compensation_deduction_definitions')->nullOnDelete();
            $table->timestamp('applied_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deduction_definition_id');
            $table->dropColumn(['calculation_type', 'frequency', 'percentage_rate', 'applied_at']);
        });

        Schema::table('payroll_allowances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('allowance_definition_id');
            $table->dropColumn(['calculation_type', 'frequency', 'percentage_rate', 'applied_at']);
        });

        Schema::dropIfExists('compensation_salary_changes');

        Schema::table('employee_compensations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('salary_template_id');
            $table->dropConstrainedForeignId('changed_by_user_id');
            $table->dropColumn([
                'risk_allowance', 'responsibility_allowance', 'payment_frequency',
                'payroll_group', 'currency', 'status', 'change_reason',
            ]);
        });

        Schema::dropIfExists('compensation_deduction_definitions');
        Schema::dropIfExists('compensation_allowance_definitions');
        Schema::dropIfExists('compensation_salary_templates');
    }
};
