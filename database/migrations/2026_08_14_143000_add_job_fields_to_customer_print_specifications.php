<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_print_specifications')) {
            return;
        }

        Schema::table('customer_print_specifications', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_print_specifications', 'production_destination')) {
                $table->string('production_destination', 30)->nullable()->after('default_fulfilment_method');
            }

            if (! Schema::hasColumn('customer_print_specifications', 'job_sheet_payload')) {
                $table->json('job_sheet_payload')->nullable()->after('production_destination');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_print_specifications')) {
            return;
        }

        Schema::table('customer_print_specifications', function (Blueprint $table) {
            if (Schema::hasColumn('customer_print_specifications', 'job_sheet_payload')) {
                $table->dropColumn('job_sheet_payload');
            }

            if (Schema::hasColumn('customer_print_specifications', 'production_destination')) {
                $table->dropColumn('production_destination');
            }
        });
    }
};
