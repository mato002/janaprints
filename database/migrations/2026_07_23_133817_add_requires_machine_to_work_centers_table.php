<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            $table->boolean('requires_machine')->default(false)->after('is_active');
        });

        if (Schema::hasTable('work_centers')) {
            DB::table('work_centers')
                ->whereIn('code', ['DIGITAL', 'OFFSET', 'LARGE_FORMAT'])
                ->update(['requires_machine' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('work_centers', function (Blueprint $table) {
            $table->dropColumn('requires_machine');
        });
    }
};
