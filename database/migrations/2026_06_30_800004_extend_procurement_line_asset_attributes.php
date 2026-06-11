<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addAssetColumns = function (string $tableName, Blueprint $table): void {
            if (! Schema::hasColumn($tableName, 'capitalization_required')) {
                $table->boolean('capitalization_required')->default(false)->after('asset_category_id');
            }
            if (! Schema::hasColumn($tableName, 'asset_useful_life')) {
                $table->unsignedSmallInteger('asset_useful_life')->nullable()->after('capitalization_required');
            }
            if (! Schema::hasColumn($tableName, 'asset_depreciation_method')) {
                $table->string('asset_depreciation_method', 50)->nullable()->after('asset_useful_life');
            }
        };

        if (Schema::hasTable('purchase_request_items')) {
            Schema::table('purchase_request_items', function (Blueprint $table) use ($addAssetColumns) {
                $addAssetColumns('purchase_request_items', $table);
            });
        }

        if (Schema::hasTable('rfq_items')) {
            Schema::table('rfq_items', function (Blueprint $table) use ($addAssetColumns) {
                if (! Schema::hasColumn('rfq_items', 'item_classification')) {
                    $table->string('item_classification', 30)->default('inventory_item')->after('inventory_item_id');
                }
                if (! Schema::hasColumn('rfq_items', 'asset_category_id')) {
                    $table->foreignId('asset_category_id')->nullable()->after('item_classification')->constrained('asset_categories')->nullOnDelete();
                }
                $addAssetColumns('rfq_items', $table);
            });
        }

        if (Schema::hasTable('purchase_order_items')) {
            Schema::table('purchase_order_items', function (Blueprint $table) use ($addAssetColumns) {
                $addAssetColumns('purchase_order_items', $table);
            });
        }

        if (Schema::hasTable('goods_receipt_items')) {
            Schema::table('goods_receipt_items', function (Blueprint $table) use ($addAssetColumns) {
                $addAssetColumns('goods_receipt_items', $table);
            });
        }

        if (Schema::hasTable('asset_capitalization_candidates')) {
            Schema::table('asset_capitalization_candidates', function (Blueprint $table) {
                if (! Schema::hasColumn('asset_capitalization_candidates', 'capitalization_required')) {
                    $table->boolean('capitalization_required')->default(true)->after('asset_category_id');
                }
                if (! Schema::hasColumn('asset_capitalization_candidates', 'asset_useful_life')) {
                    $table->unsignedSmallInteger('asset_useful_life')->nullable()->after('capitalization_required');
                }
                if (! Schema::hasColumn('asset_capitalization_candidates', 'asset_depreciation_method')) {
                    $table->string('asset_depreciation_method', 50)->nullable()->after('asset_useful_life');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('asset_capitalization_candidates', function (Blueprint $table) {
            $table->dropColumn(['capitalization_required', 'asset_useful_life', 'asset_depreciation_method']);
        });

        foreach (['goods_receipt_items', 'purchase_order_items', 'purchase_request_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['capitalization_required', 'asset_useful_life', 'asset_depreciation_method']);
            });
        }

        Schema::table('rfq_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_category_id');
            $table->dropColumn([
                'item_classification',
                'capitalization_required',
                'asset_useful_life',
                'asset_depreciation_method',
            ]);
        });
    }
};
