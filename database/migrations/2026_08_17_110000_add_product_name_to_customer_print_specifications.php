<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_print_specifications')) {
            return;
        }

        Schema::table('customer_print_specifications', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_print_specifications', 'product_name')) {
                $table->string('product_name', 255)->nullable()->after('inventory_item_id');
            }
        });

        if (! Schema::hasColumn('customer_print_specifications', 'product_name')) {
            return;
        }

        $specs = DB::table('customer_print_specifications')
            ->whereNotNull('inventory_item_id')
            ->where(function ($query) {
                $query->whereNull('product_name')->orWhere('product_name', '');
            })
            ->get(['id', 'inventory_item_id']);

        if ($specs->isEmpty()) {
            return;
        }

        $names = DB::table('inventory_items')
            ->whereIn('id', $specs->pluck('inventory_item_id')->unique()->filter()->all())
            ->pluck('item_name', 'id');

        foreach ($specs as $spec) {
            $name = $names[$spec->inventory_item_id] ?? null;

            if (filled($name)) {
                DB::table('customer_print_specifications')
                    ->where('id', $spec->id)
                    ->update(['product_name' => $name]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_print_specifications')) {
            return;
        }

        Schema::table('customer_print_specifications', function (Blueprint $table) {
            if (Schema::hasColumn('customer_print_specifications', 'product_name')) {
                $table->dropColumn('product_name');
            }
        });
    }
};
