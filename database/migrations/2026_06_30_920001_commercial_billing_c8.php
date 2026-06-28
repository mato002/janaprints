<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('sales_orders', 'billing_type')) {
                    $table->string('billing_type', 30)->default('net_30')->after('fulfilment_method');
                }
                if (! Schema::hasColumn('sales_orders', 'payment_terms_days')) {
                    $table->unsignedSmallInteger('payment_terms_days')->default(30)->after('billing_type');
                }
                if (! Schema::hasColumn('sales_orders', 'required_deposit_amount')) {
                    $table->decimal('required_deposit_amount', 15, 2)->default(0)->after('payment_terms_days');
                }
                if (! Schema::hasColumn('sales_orders', 'deposit_invoiced_amount')) {
                    $table->decimal('deposit_invoiced_amount', 15, 2)->default(0)->after('required_deposit_amount');
                }
                if (! Schema::hasColumn('sales_orders', 'deposit_paid_amount')) {
                    $table->decimal('deposit_paid_amount', 15, 2)->default(0)->after('deposit_invoiced_amount');
                }
            });
        }

        if (Schema::hasTable('customer_invoices')) {
            Schema::table('customer_invoices', function (Blueprint $table) {
                $table->index(['customer_id', 'status'], 'ci_customer_status_idx');
                $table->index(['due_date', 'status'], 'ci_due_date_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                foreach (['billing_type', 'payment_terms_days', 'required_deposit_amount', 'deposit_invoiced_amount', 'deposit_paid_amount'] as $col) {
                    if (Schema::hasColumn('sales_orders', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('customer_invoices')) {
            Schema::table('customer_invoices', function (Blueprint $table) {
                $table->dropIndex('ci_customer_status_idx');
                $table->dropIndex('ci_due_date_idx');
            });
        }
    }
};
