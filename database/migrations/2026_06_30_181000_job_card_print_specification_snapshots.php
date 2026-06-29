<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_order_items')) {
            Schema::table('sales_order_items', function (Blueprint $table) {
                if (! Schema::hasColumn('sales_order_items', 'specification_name')) {
                    $table->string('specification_name', 120)->nullable()->after('specification_code');
                }
                if (! Schema::hasColumn('sales_order_items', 'customer_instructions_snapshot')) {
                    $table->text('customer_instructions_snapshot')->nullable()->after('commercial_notes_snapshot');
                }
            });
        }

        if (Schema::hasTable('production_job_cards')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                if (Schema::hasColumn('production_job_cards', 'quotation_id')) {
                    $table->unsignedBigInteger('quotation_id')->nullable()->change();
                }
                if (Schema::hasColumn('production_job_cards', 'artwork_request_id')) {
                    $table->unsignedBigInteger('artwork_request_id')->nullable()->change();
                }

                if (! Schema::hasColumn('production_job_cards', 'customer_print_specification_id')) {
                    $table->foreignId('customer_print_specification_id')
                        ->nullable()
                        ->after('customer_artwork_id')
                        ->constrained('customer_print_specifications', indexName: 'pjc_print_spec_fk')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('production_job_cards', 'order_source')) {
                    $table->string('order_source', 20)->nullable()->after('customer_print_specification_id');
                }
                if (! Schema::hasColumn('production_job_cards', 'specification_code')) {
                    $table->string('specification_code', 40)->nullable()->after('order_source');
                }
                if (! Schema::hasColumn('production_job_cards', 'specification_name')) {
                    $table->string('specification_name', 120)->nullable()->after('specification_code');
                }
                if (! Schema::hasColumn('production_job_cards', 'artwork_version_number')) {
                    $table->unsignedSmallInteger('artwork_version_number')->nullable()->after('specification_name');
                }
                if (! Schema::hasColumn('production_job_cards', 'production_notes_snapshot')) {
                    $table->text('production_notes_snapshot')->nullable()->after('artwork_version_number');
                }
                if (! Schema::hasColumn('production_job_cards', 'commercial_notes_snapshot')) {
                    $table->text('commercial_notes_snapshot')->nullable()->after('production_notes_snapshot');
                }
                if (! Schema::hasColumn('production_job_cards', 'customer_instructions_snapshot')) {
                    $table->text('customer_instructions_snapshot')->nullable()->after('commercial_notes_snapshot');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('production_job_cards')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                foreach ([
                    'customer_instructions_snapshot',
                    'commercial_notes_snapshot',
                    'production_notes_snapshot',
                    'artwork_version_number',
                    'specification_name',
                    'specification_code',
                    'order_source',
                ] as $column) {
                    if (Schema::hasColumn('production_job_cards', $column)) {
                        $table->dropColumn($column);
                    }
                }

                if (Schema::hasColumn('production_job_cards', 'customer_print_specification_id')) {
                    $table->dropConstrainedForeignId('customer_print_specification_id');
                }
            });
        }

        if (Schema::hasTable('sales_order_items')) {
            Schema::table('sales_order_items', function (Blueprint $table) {
                foreach (['customer_instructions_snapshot', 'specification_name'] as $column) {
                    if (Schema::hasColumn('sales_order_items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
