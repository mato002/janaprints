<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_runs', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_runs', 'payroll_group')) {
                $table->string('payroll_group', 50)->default('main')->after('branch_id');
            }

            if (! Schema::hasColumn('payroll_runs', 'scope_snapshot')) {
                $table->json('scope_snapshot')->nullable()->after('review_snapshot');
            }

            if (! Schema::hasColumn('payroll_runs', 'frozen_snapshot')) {
                $table->json('frozen_snapshot')->nullable()->after('scope_snapshot');
            }
        });

        Schema::table('payroll_payslips', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_payslips', 'employee_compensation_id')) {
                $table->foreignId('employee_compensation_id')->nullable()->after('employee_id')
                    ->constrained('employee_compensations')->nullOnDelete();
            }

            if (! Schema::hasColumn('payroll_payslips', 'compensation_snapshot')) {
                $table->json('compensation_snapshot')->nullable()->after('absent_days');
            }

            if (! Schema::hasColumn('payroll_payslips', 'calculation_breakdown')) {
                $table->json('calculation_breakdown')->nullable()->after('compensation_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_payslips', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_payslips', 'calculation_breakdown')) {
                $table->dropColumn('calculation_breakdown');
            }

            if (Schema::hasColumn('payroll_payslips', 'compensation_snapshot')) {
                $table->dropColumn('compensation_snapshot');
            }

            if (Schema::hasColumn('payroll_payslips', 'employee_compensation_id')) {
                $table->dropConstrainedForeignId('employee_compensation_id');
            }
        });

        Schema::table('payroll_runs', function (Blueprint $table) {
            foreach (['frozen_snapshot', 'scope_snapshot', 'payroll_group'] as $column) {
                if (Schema::hasColumn('payroll_runs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
