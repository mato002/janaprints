<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('production_specifications')) {
            return;
        }

        if (Schema::hasColumn('production_specifications', 'job_sheet_payload')) {
            return;
        }

        Schema::table('production_specifications', function (Blueprint $table) {
            $table->json('job_sheet_payload')->nullable()->after('snapshot_payload');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('production_specifications')) {
            return;
        }

        if (! Schema::hasColumn('production_specifications', 'job_sheet_payload')) {
            return;
        }

        Schema::table('production_specifications', function (Blueprint $table) {
            $table->dropColumn('job_sheet_payload');
        });
    }
};
