<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_cost_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('draft');
            $table->decimal('material_cost', 15, 2)->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('machine_cost', 15, 2)->default(0);
            $table->decimal('finishing_cost', 15, 2)->default(0);
            $table->decimal('outsourced_cost', 15, 2)->default(0);
            $table->decimal('overhead_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('gross_margin_percent', 8, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->decimal('net_margin_percent', 8, 2)->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique('production_job_card_id');
        });

        Schema::create('job_cost_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_cost_sheet_id')->constrained()->cascadeOnDelete();
            $table->string('cost_category', 30);
            $table->string('description');
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_movement_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 12, 3)->nullable();
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->timestamps();

            $table->index('job_cost_sheet_id');
            $table->index('inventory_movement_id');
        });

        Schema::create('job_overhead_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rate_name');
            $table->string('production_type', 30)->nullable();
            $table->decimal('rate_percent', 8, 2)->default(0);
            $table->decimal('fixed_amount', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active'], 'job_overhead_rates_company_active_idx');
        });

        Schema::create('job_profitability_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('snapshot_scope', 30);
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('scope_label')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('margin_percent', 8, 2)->default(0);
            $table->unsignedInteger('job_count')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'snapshot_scope', 'period_end'], 'job_profit_snapshots_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_profitability_snapshots');
        Schema::dropIfExists('job_overhead_rates');
        Schema::dropIfExists('job_cost_lines');
        Schema::dropIfExists('job_cost_sheets');
    }
};
