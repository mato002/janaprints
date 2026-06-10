<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_profitability_snapshots')) {
            return;
        }

        Schema::create('print_profitability_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('production_job_card_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('machine_profile_id')->nullable();
            $table->string('snapshot_type', 30);
            $table->decimal('revenue', 14, 2)->default(0);
            $table->decimal('material_cost', 14, 2)->default(0);
            $table->decimal('ink_cost', 14, 2)->default(0);
            $table->decimal('machine_cost', 14, 2)->default(0);
            $table->decimal('labour_cost', 14, 2)->default(0);
            $table->decimal('electricity_cost', 14, 2)->default(0);
            $table->decimal('overhead_cost', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->decimal('gross_profit', 14, 2)->default(0);
            $table->decimal('gross_margin_percent', 8, 3)->nullable();
            $table->decimal('estimated_profit', 14, 2)->nullable();
            $table->decimal('estimated_margin_percent', 8, 3)->nullable();
            $table->decimal('profit_variance', 14, 2)->nullable();
            $table->decimal('margin_variance_percent', 8, 3)->nullable();
            $table->decimal('profitability_score', 5, 2)->nullable();
            $table->string('profitability_class', 30)->default('unknown');
            $table->date('snapshot_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('branch_id', 'pps_branch_fk')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('quotation_id', 'pps_quotation_fk')->references('id')->on('quotations')->nullOnDelete();
            $table->foreign('production_job_card_id', 'pps_job_fk')->references('id')->on('production_job_cards')->nullOnDelete();
            $table->foreign('customer_id', 'pps_customer_fk')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('machine_profile_id', 'pps_machine_fk')->references('id')->on('machine_profiles')->nullOnDelete();

            $table->index('company_id', 'pps_company_idx');
            $table->index('snapshot_type', 'pps_type_idx');
            $table->index('customer_id', 'pps_customer_idx');
            $table->index('machine_profile_id', 'pps_machine_idx');
            $table->index('profitability_class', 'pps_class_idx');
            $table->index('snapshot_date', 'pps_date_idx');
            $table->unique(['company_id', 'snapshot_type', 'production_job_card_id', 'snapshot_date'], 'pps_job_date_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_profitability_snapshots');
    }
};
