<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('target_amount', 14, 2)->default(0);
            $table->timestamps();

            $table->index(['employee_id', 'period_start', 'period_end'], 'emp_sales_target_period_idx');
        });

        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40);
            $table->string('cycle', 20);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('rating', 20)->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('production_output', 12, 2)->default(0);
            $table->decimal('sales_actual', 14, 2)->default(0);
            $table->decimal('sales_target', 14, 2)->default(0);
            $table->decimal('attendance_percent', 5, 2)->default(0);
            $table->decimal('quality_percent', 5, 2)->default(0);
            $table->decimal('job_completion_percent', 5, 2)->default(0);
            $table->decimal('customer_rating', 5, 2)->default(0);
            $table->decimal('composite_score', 5, 2)->default(0);
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('manager_notes')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'reference'], 'perf_review_ref_unique');
            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'cycle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('employee_sales_targets');
    }
};
