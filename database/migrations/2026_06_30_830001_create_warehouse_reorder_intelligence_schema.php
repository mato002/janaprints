<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_item_warehouse_reorder_settings')) {
            Schema::create('inventory_item_warehouse_reorder_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->decimal('min_level', 12, 3)->default(0);
                $table->decimal('max_level', 12, 3)->nullable();
                $table->decimal('reorder_quantity', 12, 3)->default(0);
                $table->decimal('safety_stock', 12, 3)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['warehouse_id', 'inventory_item_id'], 'inv_wh_reorder_item_unique');
            });
        }

        if (Schema::hasTable('inventory_reorder_alerts')) {
            Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
                if (! Schema::hasColumn('inventory_reorder_alerts', 'max_level')) {
                    $table->decimal('max_level', 12, 3)->nullable()->after('reorder_level');
                }
                if (! Schema::hasColumn('inventory_reorder_alerts', 'reorder_quantity')) {
                    $table->decimal('reorder_quantity', 12, 3)->default(0)->after('max_level');
                }
                if (! Schema::hasColumn('inventory_reorder_alerts', 'safety_stock')) {
                    $table->decimal('safety_stock', 12, 3)->default(0)->after('reorder_quantity');
                }
                if (! Schema::hasColumn('inventory_reorder_alerts', 'replenishment_action')) {
                    $table->string('replenishment_action', 20)->nullable()->after('safety_stock');
                }
                if (! Schema::hasColumn('inventory_reorder_alerts', 'source_warehouse_id')) {
                    $table->foreignId('source_warehouse_id')->nullable()->after('replenishment_action')
                        ->constrained('warehouses')->nullOnDelete();
                }
                if (! Schema::hasColumn('inventory_reorder_alerts', 'recommended_quantity')) {
                    $table->decimal('recommended_quantity', 12, 3)->default(0)->after('source_warehouse_id');
                }
            });

            if (Schema::hasColumn('inventory_reorder_alerts', 'warehouse_id')) {
                foreach (DB::table('inventory_reorder_alerts')->whereNull('warehouse_id')->get() as $alert) {
                    $warehouseId = DB::table('warehouses')
                        ->where('company_id', $alert->company_id)
                        ->where('branch_id', $alert->branch_id)
                        ->orderBy('id')
                        ->value('id');

                    if ($warehouseId) {
                        DB::table('inventory_reorder_alerts')
                            ->where('id', $alert->id)
                            ->update(['warehouse_id' => $warehouseId]);
                    }
                }
            }

            try {
                Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
                    $table->dropUnique('inv_reorder_alerts_tenant_item_unique');
                });
            } catch (\Throwable) {
            }

            try {
                Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
                    $table->unique(
                        ['company_id', 'branch_id', 'inventory_item_id', 'warehouse_id'],
                        'inv_reorder_wh_item_unique',
                    );
                });
            } catch (\Throwable) {
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory_reorder_alerts')) {
            try {
                Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
                    $table->dropUnique('inv_reorder_wh_item_unique');
                });
            } catch (\Throwable) {
            }

            Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
                foreach ([
                    'max_level', 'reorder_quantity', 'safety_stock',
                    'replenishment_action', 'recommended_quantity',
                ] as $column) {
                    if (Schema::hasColumn('inventory_reorder_alerts', $column)) {
                        $table->dropColumn($column);
                    }
                }
                if (Schema::hasColumn('inventory_reorder_alerts', 'source_warehouse_id')) {
                    $table->dropConstrainedForeignId('source_warehouse_id');
                }
            });

            try {
                Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
                    $table->unique(['company_id', 'branch_id', 'inventory_item_id'], 'inv_reorder_alerts_tenant_item_unique');
                });
            } catch (\Throwable) {
            }
        }

        Schema::dropIfExists('inventory_item_warehouse_reorder_settings');
    }
};
