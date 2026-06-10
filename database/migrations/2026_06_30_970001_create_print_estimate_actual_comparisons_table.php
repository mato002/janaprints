<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_estimate_actual_comparisons')) {
            return;
        }

        Schema::create('print_estimate_actual_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('print_quotation_estimate_id')->nullable();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('production_job_card_id')->nullable();
            $table->unsignedBigInteger('job_cost_sheet_id')->nullable();
            $table->unsignedBigInteger('production_output_id')->nullable();
            $table->string('comparison_status', 30)->default('pending');
            $table->decimal('estimated_material_cost', 14, 2)->default(0);
            $table->decimal('actual_material_cost', 14, 2)->default(0);
            $table->decimal('material_cost_variance', 14, 2)->default(0);
            $table->decimal('material_cost_variance_percent', 8, 3)->nullable();
            $table->decimal('estimated_ink_cost', 14, 2)->default(0);
            $table->decimal('actual_ink_cost', 14, 2)->default(0);
            $table->decimal('ink_cost_variance', 14, 2)->default(0);
            $table->decimal('ink_cost_variance_percent', 8, 3)->nullable();
            $table->decimal('estimated_machine_cost', 14, 2)->default(0);
            $table->decimal('actual_machine_cost', 14, 2)->default(0);
            $table->decimal('machine_cost_variance', 14, 2)->default(0);
            $table->decimal('machine_cost_variance_percent', 8, 3)->nullable();
            $table->decimal('estimated_labour_cost', 14, 2)->default(0);
            $table->decimal('actual_labour_cost', 14, 2)->default(0);
            $table->decimal('labour_cost_variance', 14, 2)->default(0);
            $table->decimal('labour_cost_variance_percent', 8, 3)->nullable();
            $table->decimal('estimated_overhead_cost', 14, 2)->default(0);
            $table->decimal('actual_overhead_cost', 14, 2)->default(0);
            $table->decimal('overhead_cost_variance', 14, 2)->default(0);
            $table->decimal('overhead_cost_variance_percent', 8, 3)->nullable();
            $table->decimal('estimated_total_cost', 14, 2)->default(0);
            $table->decimal('actual_total_cost', 14, 2)->default(0);
            $table->decimal('total_cost_variance', 14, 2)->default(0);
            $table->decimal('total_cost_variance_percent', 8, 3)->nullable();
            $table->decimal('recommended_price', 14, 2)->nullable();
            $table->decimal('actual_selling_price', 14, 2)->nullable();
            $table->decimal('estimated_margin_percent', 8, 3)->nullable();
            $table->decimal('actual_margin_percent', 8, 3)->nullable();
            $table->decimal('margin_variance_percent', 8, 3)->nullable();
            $table->decimal('accuracy_score', 5, 2)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('variance_class', 30)->default('unknown');
            $table->text('recommendation')->nullable();
            $table->json('calculation_breakdown')->nullable();
            $table->json('warnings')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('compared_at')->nullable();
            $table->timestamps();

            $table->foreign('branch_id', 'pea_cmp_branch_fk')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('print_quotation_estimate_id', 'pea_cmp_estimate_fk')
                ->references('id')->on('print_quotation_estimates')->nullOnDelete();
            $table->foreign('quotation_id', 'pea_cmp_quotation_fk')->references('id')->on('quotations')->nullOnDelete();
            $table->foreign('production_job_card_id', 'pea_cmp_job_fk')
                ->references('id')->on('production_job_cards')->nullOnDelete();
            $table->foreign('job_cost_sheet_id', 'pea_cmp_sheet_fk')
                ->references('id')->on('job_cost_sheets')->nullOnDelete();
            $table->foreign('production_output_id', 'pea_cmp_output_fk')
                ->references('id')->on('production_outputs')->nullOnDelete();

            $table->index('company_id', 'pea_cmp_company_idx');
            $table->index('quotation_id', 'pea_cmp_quotation_idx');
            $table->index('production_job_card_id', 'pea_cmp_job_idx');
            $table->index('comparison_status', 'pea_cmp_status_idx');
            $table->index('variance_class', 'pea_cmp_variance_idx');
            $table->index('accuracy_score', 'pea_cmp_accuracy_idx');
            $table->unique('print_quotation_estimate_id', 'pea_cmp_estimate_uq');
            $table->unique('production_job_card_id', 'pea_cmp_job_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_estimate_actual_comparisons');
    }
};
