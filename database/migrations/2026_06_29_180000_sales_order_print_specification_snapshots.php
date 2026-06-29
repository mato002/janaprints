<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_orders') && ! Schema::hasColumn('sales_orders', 'customer_print_specification_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->foreignId('customer_print_specification_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('customer_print_specifications', indexName: 'so_print_spec_fk')
                    ->nullOnDelete();
                $table->string('priority', 20)->default('normal')->after('required_date');
                $table->index(['customer_id', 'customer_print_specification_id'], 'so_customer_print_spec_idx');
            });
        }

        if (Schema::hasTable('sales_order_items')) {
            Schema::table('sales_order_items', function (Blueprint $table) {
                if (! Schema::hasColumn('sales_order_items', 'customer_print_specification_id')) {
                    $table->foreignId('customer_print_specification_id')
                        ->nullable()
                        ->after('sales_order_id')
                        ->constrained('customer_print_specifications', indexName: 'soi_print_spec_fk')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('sales_order_items', 'customer_artwork_id')) {
                    $table->foreignId('customer_artwork_id')
                        ->nullable()
                        ->after('inventory_item_id')
                        ->constrained('customer_artworks', indexName: 'soi_customer_artwork_fk')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('sales_order_items', 'specification_code')) {
                    $table->string('specification_code', 40)->nullable()->after('customer_artwork_id');
                }
                if (! Schema::hasColumn('sales_order_items', 'artwork_version_number')) {
                    $table->unsignedSmallInteger('artwork_version_number')->nullable()->after('specification_code');
                }
                if (! Schema::hasColumn('sales_order_items', 'production_notes_snapshot')) {
                    $table->text('production_notes_snapshot')->nullable()->after('description');
                }
                if (! Schema::hasColumn('sales_order_items', 'commercial_notes_snapshot')) {
                    $table->text('commercial_notes_snapshot')->nullable()->after('production_notes_snapshot');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_order_items')) {
            Schema::table('sales_order_items', function (Blueprint $table) {
                foreach ([
                    'commercial_notes_snapshot',
                    'production_notes_snapshot',
                    'artwork_version_number',
                    'specification_code',
                    'customer_artwork_id',
                    'customer_print_specification_id',
                ] as $column) {
                    if (Schema::hasColumn('sales_order_items', $column)) {
                        if (str_ends_with($column, '_id')) {
                            $table->dropConstrainedForeignId($column);
                        } else {
                            $table->dropColumn($column);
                        }
                    }
                }
            });
        }

        if (Schema::hasTable('sales_orders') && Schema::hasColumn('sales_orders', 'customer_print_specification_id')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('customer_print_specification_id');
                if (Schema::hasColumn('sales_orders', 'priority')) {
                    $table->dropColumn('priority');
                }
            });
        }
    }
};
