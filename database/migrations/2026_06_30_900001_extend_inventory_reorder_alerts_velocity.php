<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_reorder_alerts')) {
            return;
        }

        Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_reorder_alerts', 'alert_type')) {
                $table->string('alert_type', 40)->default('reorder_level')->after('warehouse_id');
            }
            if (! Schema::hasColumn('inventory_reorder_alerts', 'metadata')) {
                $table->json('metadata')->nullable()->after('recommended_quantity');
            }
        });

        if (Schema::hasColumn('inventory_reorder_alerts', 'alert_type')) {
            DB::table('inventory_reorder_alerts')
                ->whereNull('alert_type')
                ->update(['alert_type' => 'reorder_level']);
        }

        try {
            Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
                $table->dropUnique('inv_reorder_wh_item_unique');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
                $table->unique(
                    ['company_id', 'branch_id', 'inventory_item_id', 'warehouse_id', 'alert_type'],
                    'inv_reorder_wh_item_type_unique',
                );
            });
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventory_reorder_alerts')) {
            return;
        }

        try {
            Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
                $table->dropUnique('inv_reorder_wh_item_type_unique');
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

        Schema::table('inventory_reorder_alerts', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_reorder_alerts', 'alert_type')) {
                $table->dropColumn('alert_type');
            }
            if (Schema::hasColumn('inventory_reorder_alerts', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
