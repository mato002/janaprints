<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_quotation_estimates')) {
            return;
        }

        Schema::create('print_quotation_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('print_artwork_analysis_id');
            $table->unsignedBigInteger('print_artwork_ink_estimate_id')->nullable();
            $table->unsignedBigInteger('print_machine_estimate_id')->nullable();
            $table->string('estimation_status', 30)->default('draft');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedSmallInteger('version')->default(1);
            $table->unsignedBigInteger('material_inventory_item_id')->nullable();
            $table->string('material_name', 200)->nullable();
            $table->decimal('material_unit_cost', 14, 4)->nullable();
            $table->decimal('material_quantity', 14, 6)->nullable();
            $table->decimal('estimated_material_cost', 14, 2)->default(0);
            $table->decimal('estimated_ink_cost', 14, 2)->default(0);
            $table->decimal('estimated_machine_cost', 14, 2)->default(0);
            $table->decimal('estimated_labour_cost', 14, 2)->default(0);
            $table->decimal('estimated_electricity_cost', 14, 2)->default(0);
            $table->decimal('estimated_overhead_cost', 14, 2)->default(0);
            $table->decimal('estimated_wastage_cost', 14, 2)->default(0);
            $table->decimal('estimated_total_cost', 14, 2)->default(0);
            $table->decimal('minimum_margin_percent', 8, 3)->default(0);
            $table->decimal('target_margin_percent', 8, 3)->default(0);
            $table->decimal('minimum_selling_price', 14, 2)->default(0);
            $table->decimal('recommended_selling_price', 14, 2)->default(0);
            $table->decimal('expected_margin_percent', 8, 3)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('formula_version', 40)->nullable();
            $table->json('calculation_breakdown')->nullable();
            $table->json('warnings')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->unsignedBigInteger('applied_by')->nullable();
            $table->timestamps();

            $table->foreign('branch_id', 'pq_est_branch_fk')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('quotation_id', 'pq_est_quotation_fk')->references('id')->on('quotations')->nullOnDelete();
            $table->foreign('print_artwork_analysis_id', 'pq_est_analysis_fk')
                ->references('id')->on('print_artwork_analyses')->cascadeOnDelete();
            $table->foreign('print_artwork_ink_estimate_id', 'pq_est_ink_fk')
                ->references('id')->on('print_artwork_ink_estimates')->nullOnDelete();
            $table->foreign('print_machine_estimate_id', 'pq_est_machine_fk')
                ->references('id')->on('print_artwork_production_estimates')->nullOnDelete();
            $table->foreign('applied_by', 'pq_est_applied_by_fk')->references('id')->on('users')->nullOnDelete();

            $table->index('company_id', 'pq_est_company_idx');
            $table->index('quotation_id', 'pq_est_quotation_idx');
            $table->index('print_artwork_analysis_id', 'pq_est_analysis_idx');
            $table->index('estimation_status', 'pq_est_status_idx');
            $table->unique(
                ['print_artwork_analysis_id', 'quantity', 'material_inventory_item_id', 'version'],
                'pq_est_analysis_qty_material_ver_uq',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_quotation_estimates');
    }
};
