<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units_of_measure', function (Blueprint $table) {
            $table->foreignId('base_unit_id')->nullable()->after('name')->constrained('units_of_measure')->nullOnDelete();
            $table->decimal('conversion_factor', 12, 4)->default(1)->after('base_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('units_of_measure', function (Blueprint $table) {
            $table->dropConstrainedForeignId('base_unit_id');
            $table->dropColumn('conversion_factor');
        });
    }
};
