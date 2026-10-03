<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('production_queues') || ! Schema::hasColumn('production_queues', 'work_center_id')) {
            return;
        }

        Schema::table('production_queues', function (Blueprint $table) {
            $table->dropForeign(['work_center_id']);
        });

        Schema::table('production_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('work_center_id')->nullable()->change();
        });

        Schema::table('production_queues', function (Blueprint $table) {
            $table->foreign('work_center_id')
                ->references('id')
                ->on('work_centers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('production_queues') || ! Schema::hasColumn('production_queues', 'work_center_id')) {
            return;
        }

        Schema::table('production_queues', function (Blueprint $table) {
            $table->dropForeign(['work_center_id']);
        });

        Schema::table('production_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('work_center_id')->nullable(false)->change();
        });

        Schema::table('production_queues', function (Blueprint $table) {
            $table->foreign('work_center_id')
                ->references('id')
                ->on('work_centers')
                ->cascadeOnDelete();
        });
    }
};
