<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tax_returns')) {
            return;
        }

        Schema::table('tax_returns', function (Blueprint $table) {
            if (! Schema::hasColumn('tax_returns', 'filing_package_path')) {
                $table->string('filing_package_path')->nullable()->after('filed_at');
            }
            if (! Schema::hasColumn('tax_returns', 'filing_package_checksum')) {
                $table->string('filing_package_checksum', 64)->nullable()->after('filing_package_path');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tax_returns')) {
            return;
        }

        Schema::table('tax_returns', function (Blueprint $table) {
            if (Schema::hasColumn('tax_returns', 'filing_package_checksum')) {
                $table->dropColumn('filing_package_checksum');
            }
            if (Schema::hasColumn('tax_returns', 'filing_package_path')) {
                $table->dropColumn('filing_package_path');
            }
        });
    }
};
