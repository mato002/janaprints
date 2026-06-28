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
            if (Schema::hasColumn('sales_orders', 'quotation_id')) {
                $table->dropForeign(['quotation_id']);
            }
            if (Schema::hasColumn('sales_orders', 'artwork_request_id')) {
                $table->dropForeign(['artwork_request_id']);
            }
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_id')->nullable()->change();
            $table->unsignedBigInteger('artwork_request_id')->nullable()->change();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreign('quotation_id')->references('id')->on('quotations')->nullOnDelete();
            $table->foreign('artwork_request_id')->references('id')->on('artwork_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sales_orders')) {
            return;
        }

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropForeign(['artwork_request_id']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('quotation_id')->nullable(false)->change();
            $table->unsignedBigInteger('artwork_request_id')->nullable(false)->change();
            $table->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $table->foreign('artwork_request_id')->references('id')->on('artwork_requests')->cascadeOnDelete();
        });
    }
};
