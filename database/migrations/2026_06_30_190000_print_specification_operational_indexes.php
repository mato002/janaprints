<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->index(['customer_print_specification_id', 'order_date'], 'sales_orders_spec_order_date_idx');
        });

        Schema::table('production_job_cards', function (Blueprint $table) {
            $table->index('customer_print_specification_id', 'production_job_cards_spec_idx');
        });

        Schema::table('customer_print_specifications', function (Blueprint $table) {
            $table->index(['company_id', 'name'], 'customer_print_specs_company_name_idx');
            $table->index(['company_id', 'status'], 'customer_print_specs_company_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex('sales_orders_spec_order_date_idx');
        });

        Schema::table('production_job_cards', function (Blueprint $table) {
            $table->dropIndex('production_job_cards_spec_idx');
        });

        Schema::table('customer_print_specifications', function (Blueprint $table) {
            $table->dropIndex('customer_print_specs_company_name_idx');
            $table->dropIndex('customer_print_specs_company_status_idx');
        });
    }
};
