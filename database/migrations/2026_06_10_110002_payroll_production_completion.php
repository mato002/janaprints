<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('nssf_number');
            }
            if (! Schema::hasColumn('employees', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('employees', 'bank_branch_code')) {
                $table->string('bank_branch_code', 20)->nullable()->after('bank_account_number');
            }
        });

        Schema::table('payroll_payslips', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_payslips', 'released_at')) {
                $table->timestamp('released_at')->nullable()->after('emailed_at');
            }
        });

        Schema::table('payroll_runs', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_runs', 'employer_nssf_total')) {
                $table->decimal('employer_nssf_total', 14, 2)->default(0)->after('housing_levy_total');
            }
            if (! Schema::hasColumn('payroll_runs', 'employer_shif_total')) {
                $table->decimal('employer_shif_total', 14, 2)->default(0)->after('employer_nssf_total');
            }
            if (! Schema::hasColumn('payroll_runs', 'employer_housing_levy_total')) {
                $table->decimal('employer_housing_levy_total', 14, 2)->default(0)->after('employer_shif_total');
            }
            if (! Schema::hasColumn('payroll_runs', 'review_snapshot')) {
                $table->json('review_snapshot')->nullable()->after('has_generation_warnings');
            }
            if (! Schema::hasColumn('payroll_runs', 'has_critical_review_issues')) {
                $table->boolean('has_critical_review_issues')->default(false)->after('review_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            foreach (['bank_name', 'bank_account_number', 'bank_branch_code'] as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('payroll_payslips', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_payslips', 'released_at')) {
                $table->dropColumn('released_at');
            }
        });

        Schema::table('payroll_runs', function (Blueprint $table) {
            foreach ([
                'employer_nssf_total',
                'employer_shif_total',
                'employer_housing_levy_total',
                'review_snapshot',
                'has_critical_review_issues',
            ] as $column) {
                if (Schema::hasColumn('payroll_runs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
