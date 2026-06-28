<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_items', 'brand_name')) {
                $table->string('brand_name')->nullable()->after('brand_id');
            }
        });

        if (Schema::hasColumn('inventory_items', 'brand_id') && Schema::hasTable('brands')) {
            DB::table('inventory_items')
                ->whereNotNull('brand_id')
                ->orderBy('id')
                ->chunkById(100, function ($items): void {
                    foreach ($items as $item) {
                        $brandName = DB::table('brands')->where('id', $item->brand_id)->value('name');

                        if (filled($brandName)) {
                            DB::table('inventory_items')
                                ->where('id', $item->id)
                                ->update(['brand_name' => $brandName]);
                        }
                    }
                });
        }

        if (Schema::hasColumn('inventory_items', 'item_code')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropIndex(['item_code']);
                $table->dropColumn('item_code');
            });
        }

        if (Schema::hasTable('item_attributes')) {
            $finishIds = DB::table('item_attributes')->where('code', 'FINISH')->pluck('id');

            if ($finishIds->isNotEmpty() && Schema::hasTable('inventory_item_attributes')) {
                DB::table('inventory_item_attributes')
                    ->whereIn('item_attribute_id', $finishIds)
                    ->delete();
            }

            DB::table('item_attributes')->where('code', 'FINISH')->delete();

            DB::table('item_attributes')
                ->where('code', 'VOLUME')
                ->update(['name' => 'Amount (litres/kg)']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('item_attributes')) {
            DB::table('item_attributes')
                ->where('code', 'VOLUME')
                ->update(['name' => 'Volume']);
        }

        Schema::table('inventory_items', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_items', 'item_code')) {
                $table->string('item_code')->nullable()->after('sku');
                $table->index(['item_code']);
            }

            if (Schema::hasColumn('inventory_items', 'brand_name')) {
                $table->dropColumn('brand_name');
            }
        });
    }
};
