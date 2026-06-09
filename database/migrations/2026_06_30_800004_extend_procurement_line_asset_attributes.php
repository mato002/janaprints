<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $assetColumns = function (Blueprint $table): void {
            $table->boolean('capitalization_required')->default(false)->after('asset_category_id');
            $table->unsignedSmallInteger('asset_useful_life')->nullable()->after('capitalization_required');
            $table->string('asset_depreciation_method', 50)->nullable()->after('asset_useful_life');
        };

        Schema::table('purchase_request_items', function (Blueprint $table) use ($assetColumns) {
            $assetColumns($table);
        });

        Schema::table('rfq_items', function (Blueprint $table) use ($assetColumns) {
            $table->string('item_classification', 30)->default('inventory_item')->after('inventory_item_id');
            $table->foreignId('asset_category_id')->nullable()->after('item_classification')->constrained('asset_categories')->nullOnDelete();
            $assetColumns($table);
        });

        Schema::table('purchase_order_items', function (Blueprint $table) use ($assetColumns) {
            $assetColumns($table);
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) use ($assetColumns) {
            $assetColumns($table);
        });

        Schema::table('asset_capitalization_candidates', function (Blueprint $table) {
            $table->boolean('capitalization_required')->default(true)->after('asset_category_id');
            $table->unsignedSmallInteger('asset_useful_life')->nullable()->after('capitalization_required');
            $table->string('asset_depreciation_method', 50)->nullable()->after('asset_useful_life');
        });
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
