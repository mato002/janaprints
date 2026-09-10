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
            if (! Schema::hasColumn('customer_print_specifications', 'default_priority')) {
                $table->string('default_priority', 20)->nullable()->after('default_fulfilment_method');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_print_specifications')) {
            return;
        }

        Schema::table('customer_print_specifications', function (Blueprint $table) {
            if (Schema::hasColumn('customer_print_specifications', 'default_priority')) {
                $table->dropColumn('default_priority');
            }
        });
    }
};
