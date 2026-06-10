<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_items') && ! Schema::hasColumn('inventory_items', 'stock_role')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->string('stock_role', 30)->default('other')->after('is_active');
                $table->index(['company_id', 'branch_id', 'stock_role'], 'inventory_items_stock_role_idx');
            });

            $this->backfillStockRoles();
        }

        if (! Schema::hasTable('inventory_variance_reason_codes')) {
            Schema::create('inventory_variance_reason_codes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('code', 50);
                $table->string('name');
                $table->string('category', 40);
                $table->boolean('requires_comment')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'code'], 'inv_var_reason_co_code_uniq');
                $table->index(['company_id', 'is_active'], 'inv_var_reason_co_active_idx');
            });
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_variance_reason_codes');

        if (Schema::hasTable('inventory_items') && Schema::hasColumn('inventory_items', 'stock_role')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropIndex('inventory_items_stock_role_idx');
                $table->dropColumn('stock_role');
            });
        }
    }

    protected function backfillStockRoles(): void
    {
        if (! Schema::hasTable('inventory_categories')) {
            return;
        }

        $rawMaterialCodes = ['PAPER', 'INK', 'BANNER', 'VINYL', 'FINISHING', 'GENERAL', 'TSHIRT'];
        $packagingCodes = ['PACKAGING'];
        $finishedCodes = ['BCARD', 'FLYER', 'BROCHURE', 'STICKER', 'RECEIPT'];

        DB::table('inventory_items')
            ->join('inventory_categories', 'inventory_items.inventory_category_id', '=', 'inventory_categories.id')
            ->whereIn('inventory_categories.code', $rawMaterialCodes)
            ->update(['inventory_items.stock_role' => 'raw_material']);

        DB::table('inventory_items')
            ->join('inventory_categories', 'inventory_items.inventory_category_id', '=', 'inventory_categories.id')
            ->whereIn('inventory_categories.code', $packagingCodes)
            ->update(['inventory_items.stock_role' => 'packaging']);

        DB::table('inventory_items')
            ->join('inventory_categories', 'inventory_items.inventory_category_id', '=', 'inventory_categories.id')
            ->whereIn('inventory_categories.code', $finishedCodes)
            ->update(['inventory_items.stock_role' => 'finished_good']);
    }
};
