<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales_orders') && ! Schema::hasColumn('sales_orders', 'production_destination')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->string('production_destination', 30)->nullable()->after('priority');
                $table->index(['company_id', 'production_destination']);
            });
        }

        if (Schema::hasTable('production_job_cards') && ! Schema::hasColumn('production_job_cards', 'production_destination')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                $table->string('production_destination', 30)->nullable()->after('production_type');
                $table->index(['company_id', 'production_destination']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales_orders') && Schema::hasColumn('sales_orders', 'production_destination')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropIndex(['company_id', 'production_destination']);
                $table->dropColumn('production_destination');
            });
        }

        if (Schema::hasTable('production_job_cards') && Schema::hasColumn('production_job_cards', 'production_destination')) {
            Schema::table('production_job_cards', function (Blueprint $table) {
                $table->dropIndex(['company_id', 'production_destination']);
                $table->dropColumn('production_destination');
            });
        }
    }
};
