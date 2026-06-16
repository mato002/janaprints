<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_runs', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_runs', 'generation_warnings')) {
                $table->json('generation_warnings')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('payroll_runs', 'has_generation_warnings')) {
                $table->boolean('has_generation_warnings')->default(false)->after('generation_warnings');
            }

            if (! Schema::hasColumn('payroll_runs', 'reviewed_by_user_id')) {
                $table->foreignId('reviewed_by_user_id')->nullable()->after('processed_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('payroll_runs', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
            }

            if (! Schema::hasColumn('payroll_runs', 'submitted_for_approval_by_user_id')) {
                $table->foreignId('submitted_for_approval_by_user_id')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('payroll_runs', 'submitted_for_approval_at')) {
                $table->timestamp('submitted_for_approval_at')->nullable()->after('submitted_for_approval_by_user_id');
            }

            if (! Schema::hasColumn('payroll_runs', 'paid_by_user_id')) {
                $table->foreignId('paid_by_user_id')->nullable()->after('posted_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('payroll_runs', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('paid_by_user_id');
            }

            if (! Schema::hasColumn('payroll_runs', 'cancelled_by_user_id')) {
                $table->foreignId('cancelled_by_user_id')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('payroll_runs', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by_user_id');
            }
        });

        if (Schema::hasTable('payroll_runs')) {
            DB::table('payroll_runs')
                ->where('status', 'calculated')
                ->update(['status' => 'generated']);
        }
    }

    public function down(): void
    {
        Schema::table('payroll_runs', function (Blueprint $table) {
            foreach ([
                'generation_warnings',
                'has_generation_warnings',
                'reviewed_by_user_id',
                'reviewed_at',
                'submitted_for_approval_by_user_id',
                'submitted_for_approval_at',
                'paid_by_user_id',
                'paid_at',
                'cancelled_by_user_id',
                'cancelled_at',
            ] as $column) {
                if (Schema::hasColumn('payroll_runs', $column)) {
                    if (str_ends_with($column, '_user_id')) {
                        $table->dropConstrainedForeignId($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });

        if (Schema::hasTable('payroll_runs')) {
            DB::table('payroll_runs')
                ->where('status', 'generated')
                ->update(['status' => 'calculated']);
        }
    }
};
