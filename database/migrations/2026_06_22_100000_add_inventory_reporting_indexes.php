<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_valuations')) {
            Schema::table('inventory_valuations', function (Blueprint $table) {
                if (! $this->hasIndex('inventory_valuations', 'inv_valuations_reporting_scope_idx')) {
                    $table->index(['company_id', 'branch_id', 'warehouse_id'], 'inv_valuations_reporting_scope_idx');
                }
                if (! $this->hasIndex('inventory_valuations', 'inv_valuations_reporting_qty_idx')) {
                    $table->index(['company_id', 'quantity_on_hand'], 'inv_valuations_reporting_qty_idx');
                }
            });
        }

        if (Schema::hasTable('inventory_items')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                if (! $this->hasIndex('inventory_items', 'inv_items_reporting_reorder_idx')) {
                    $table->index(['company_id', 'branch_id', 'reorder_level'], 'inv_items_reporting_reorder_idx');
                }
            });
        }

        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                if (! $this->hasIndex('inventory_movements', 'inv_movements_reporting_activity_idx')) {
                    $table->index(['company_id', 'branch_id', 'movement_type', 'movement_date'], 'inv_movements_reporting_activity_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory_valuations')) {
            Schema::table('inventory_valuations', function (Blueprint $table) {
                $table->dropIndex('inv_valuations_reporting_scope_idx');
                $table->dropIndex('inv_valuations_reporting_qty_idx');
            });
        }

        if (Schema::hasTable('inventory_items')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropIndex('inv_items_reporting_reorder_idx');
            });
        }

        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->dropIndex('inv_movements_reporting_activity_idx');
            });
        }
    }

    protected function hasIndex(string $table, string $index): bool
    {
        foreach (Schema::getIndexes($table) as $definition) {
            if (($definition['name'] ?? '') === $index) {
                return true;
            }
        }

        return false;
    }
};
