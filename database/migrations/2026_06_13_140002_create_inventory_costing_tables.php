<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_cost_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_movement_id')->constrained('inventory_movements')->cascadeOnDelete();
            $table->decimal('quantity_received', 12, 3);
            $table->decimal('quantity_remaining', 12, 3);
            $table->decimal('unit_cost', 15, 2);
            $table->date('layer_date');
            $table->timestamps();

            $table->index(['inventory_item_id', 'warehouse_id', 'layer_date'], 'cost_layers_item_wh_date_idx');
            $table->index('inventory_movement_id');
        });

        Schema::create('inventory_valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_on_hand', 12, 3)->default(0);
            $table->decimal('average_unit_cost', 15, 2)->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['inventory_item_id', 'warehouse_id'], 'inventory_valuations_item_wh_unique');
            $table->index(['company_id', 'branch_id'], 'inventory_valuations_tenant_idx');
        });

        Schema::create('inventory_valuation_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('valuation_date');
            $table->string('snapshot_scope', 30);
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_category_id')->nullable()->constrained('inventory_categories')->nullOnDelete();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->decimal('fifo_value', 15, 2)->default(0);
            $table->decimal('average_cost_value', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['company_id', 'valuation_date'], 'valuation_snapshots_date_idx');
            $table->index(['inventory_item_id', 'valuation_date'], 'valuation_snapshots_item_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_valuation_snapshots');
        Schema::dropIfExists('inventory_valuations');
        Schema::dropIfExists('inventory_cost_layers');
    }
};
