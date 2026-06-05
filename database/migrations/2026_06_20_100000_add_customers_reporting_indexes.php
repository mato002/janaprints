<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index(['company_id', 'branch_id', 'status'], 'customers_reporting_scope_idx');
                $table->index(['company_id', 'customer_type', 'created_at'], 'customers_reporting_type_idx');
                $table->index(['company_id', 'created_at'], 'customers_reporting_created_idx');
            });
        }

        if (Schema::hasTable('customer_activities')) {
            Schema::table('customer_activities', function (Blueprint $table) {
                $table->index(['company_id', 'branch_id', 'activity_at'], 'customer_activities_reporting_scope_idx');
                $table->index(['company_id', 'customer_id', 'activity_at'], 'customer_activities_reporting_customer_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex('customers_reporting_scope_idx');
                $table->dropIndex('customers_reporting_type_idx');
                $table->dropIndex('customers_reporting_created_idx');
            });
        }

        if (Schema::hasTable('customer_activities')) {
            Schema::table('customer_activities', function (Blueprint $table) {
                $table->dropIndex('customer_activities_reporting_scope_idx');
                $table->dropIndex('customer_activities_reporting_customer_idx');
            });
        }
    }
};
