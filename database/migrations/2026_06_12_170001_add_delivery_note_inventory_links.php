<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_notes', 'posted_journal_id')) {
                $table->foreignId('posted_journal_id')
                    ->nullable()
                    ->after('delivered_at')
                    ->constrained('journals')
                    ->nullOnDelete();
                $table->index('posted_journal_id', 'delivery_notes_journal_idx');
            }
        });

        Schema::table('delivery_note_items', function (Blueprint $table) {
            if (! Schema::hasColumn('delivery_note_items', 'inventory_item_id')) {
                $table->foreignId('inventory_item_id')
                    ->nullable()
                    ->after('sales_order_item_id')
                    ->constrained('inventory_items')
                    ->nullOnDelete();
                $table->index('inventory_item_id', 'dn_items_inventory_idx');
            }

            if (! Schema::hasColumn('delivery_note_items', 'production_output_id')) {
                $table->foreignId('production_output_id')
                    ->nullable()
                    ->after('inventory_item_id')
                    ->constrained('production_outputs')
                    ->nullOnDelete();
                $table->index('production_output_id', 'dn_items_output_idx');
            }

            if (! Schema::hasColumn('delivery_note_items', 'unit_cost')) {
                $table->decimal('unit_cost', 14, 4)->nullable()->after('quantity');
            }

            if (! Schema::hasColumn('delivery_note_items', 'total_cost')) {
                $table->decimal('total_cost', 14, 2)->nullable()->after('unit_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_note_items', 'production_output_id')) {
                $table->dropConstrainedForeignId('production_output_id');
            }
            if (Schema::hasColumn('delivery_note_items', 'inventory_item_id')) {
                $table->dropConstrainedForeignId('inventory_item_id');
            }
            if (Schema::hasColumn('delivery_note_items', 'total_cost')) {
                $table->dropColumn('total_cost');
            }
            if (Schema::hasColumn('delivery_note_items', 'unit_cost')) {
                $table->dropColumn('unit_cost');
            }
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_notes', 'posted_journal_id')) {
                $table->dropConstrainedForeignId('posted_journal_id');
            }
        });
    }
};
