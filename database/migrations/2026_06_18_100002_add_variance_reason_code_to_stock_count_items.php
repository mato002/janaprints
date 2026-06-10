<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('stock_count_items')
            && Schema::hasTable('inventory_variance_reason_codes')
            && ! Schema::hasColumn('stock_count_items', 'inventory_variance_reason_code_id')
        ) {
            Schema::table('stock_count_items', function (Blueprint $table) {
                $table->foreignId('inventory_variance_reason_code_id')
                    ->nullable()
                    ->after('variance_value')
                    ->constrained('inventory_variance_reason_codes')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_count_items') && Schema::hasColumn('stock_count_items', 'inventory_variance_reason_code_id')) {
            Schema::table('stock_count_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('inventory_variance_reason_code_id');
            });
        }
    }
};
