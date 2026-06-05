<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_orders')) {
            return;
        }

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->index(['company_id', 'branch_id', 'order_date'], 'sales_orders_reporting_scope_idx');
            $table->index(['company_id', 'status', 'order_date'], 'sales_orders_reporting_status_idx');
            $table->index(['company_id', 'customer_id', 'order_date'], 'sales_orders_reporting_customer_idx');
            $table->index(['company_id', 'created_by', 'order_date'], 'sales_orders_reporting_salesperson_idx');
        });

        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->index(['company_id', 'branch_id', 'quotation_date'], 'quotations_reporting_scope_idx');
                $table->index(['company_id', 'prepared_by', 'quotation_date'], 'quotations_reporting_salesperson_idx');
                $table->index(['company_id', 'status', 'valid_until'], 'quotations_reporting_lost_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_orders')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropIndex('sales_orders_reporting_scope_idx');
                $table->dropIndex('sales_orders_reporting_status_idx');
                $table->dropIndex('sales_orders_reporting_customer_idx');
                $table->dropIndex('sales_orders_reporting_salesperson_idx');
            });
        }

        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropIndex('quotations_reporting_scope_idx');
                $table->dropIndex('quotations_reporting_salesperson_idx');
                $table->dropIndex('quotations_reporting_lost_idx');
            });
        }
    }
};
