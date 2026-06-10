<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_artwork_production_estimates')) {
            return;
        }

        Schema::create('print_artwork_production_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('print_artwork_analysis_id');
            $table->unsignedBigInteger('machine_profile_id')->nullable();
            $table->string('estimation_status', 30)->default('pending');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_area_sq_m', 14, 6)->nullable();
            $table->decimal('estimated_run_hours', 10, 4)->nullable();
            $table->decimal('estimated_setup_cost', 14, 2)->nullable();
            $table->decimal('estimated_electricity_cost', 14, 2)->nullable();
            $table->decimal('estimated_machine_cost', 14, 2)->nullable();
            $table->decimal('estimated_labour_cost', 14, 2)->nullable();
            $table->decimal('estimated_ink_cost', 14, 2)->nullable();
            $table->decimal('estimated_material_cost', 14, 2)->nullable();
            $table->decimal('estimated_overhead_cost', 14, 2)->nullable();
            $table->decimal('estimated_total_production_cost', 14, 2)->nullable();
            $table->decimal('selection_score', 8, 3)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('formula_version', 40)->nullable();
            $table->json('machine_alternatives')->nullable();
            $table->json('metadata')->nullable();
            $table->json('warnings')->nullable();
            $table->timestamp('estimated_at')->nullable();
            $table->timestamps();

            $table->foreign('print_artwork_analysis_id', 'pa_prod_est_analysis_fk')
                ->references('id')->on('print_artwork_analyses')->cascadeOnDelete();
            $table->foreign('machine_profile_id', 'pa_prod_est_machine_fk')
                ->references('id')->on('machine_profiles')->nullOnDelete();

            $table->index('company_id', 'pa_prod_est_company_idx');
            $table->index('print_artwork_analysis_id', 'pa_prod_est_analysis_idx');
            $table->index('machine_profile_id', 'pa_prod_est_machine_idx');
            $table->index('estimation_status', 'pa_prod_est_status_idx');
            $table->unique('print_artwork_analysis_id', 'pa_prod_est_analysis_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_artwork_production_estimates');
    }
};
