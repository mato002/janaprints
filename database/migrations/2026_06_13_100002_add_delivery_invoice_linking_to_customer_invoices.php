<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_invoices', 'delivery_note_id')) {
                $table->foreignId('delivery_note_id')->nullable()->after('production_job_card_id')
                    ->constrained('delivery_notes')->nullOnDelete();
            }
            if (! Schema::hasColumn('customer_invoices', 'billing_source')) {
                $table->string('billing_source', 30)->default('delivery_note')->after('delivery_note_id');
            }

            $table->index('delivery_note_id', 'customer_invoices_delivery_note_id_idx');
        });

        Schema::table('customer_invoice_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_invoice_lines', 'delivery_note_item_id')) {
                $table->foreignId('delivery_note_item_id')->nullable()->after('sales_order_item_id')
                    ->constrained('delivery_note_items')->nullOnDelete();
            }

            $table->index('delivery_note_item_id', 'customer_invoice_lines_delivery_note_item_idx');
        });
    }

    public function down(): void
    {
        Schema::table('customer_invoice_lines', function (Blueprint $table) {
            if (Schema::hasColumn('customer_invoice_lines', 'delivery_note_item_id')) {
                $table->dropConstrainedForeignId('delivery_note_item_id');
            }
        });

        Schema::table('customer_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('customer_invoices', 'delivery_note_id')) {
                $table->dropConstrainedForeignId('delivery_note_id');
            }
            if (Schema::hasColumn('customer_invoices', 'billing_source')) {
                $table->dropColumn('billing_source');
            }
        });
    }
};
