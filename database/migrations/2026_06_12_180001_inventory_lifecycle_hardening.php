<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('production_outputs') && ! Schema::hasColumn('production_outputs', 'posted_job_marker')) {
            Schema::table('production_outputs', function (Blueprint $table) {
                $table->unsignedBigInteger('posted_job_marker')->nullable()->after('production_job_card_id');
                $table->unique('posted_job_marker', 'prod_outputs_one_posted_per_job');
            });

            $rows = DB::table('production_outputs')
                ->where('completion_status', 'posted')
                ->orderBy('id')
                ->get(['id', 'production_job_card_id']);

            foreach ($rows as $row) {
                DB::table('production_outputs')
                    ->where('id', $row->id)
                    ->update(['posted_job_marker' => $row->production_job_card_id]);
            }
        }

        if (Schema::hasTable('inventory_movements') && ! Schema::hasColumn('inventory_movements', 'lifecycle_receipt_key')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->string('lifecycle_receipt_key', 120)->nullable()->after('reference_id');
                $table->unique('lifecycle_receipt_key', 'inv_movements_lifecycle_receipt_unique');
            });

            $movementRows = DB::table('inventory_movements')
                ->where('movement_type', 'finished_goods_receipt')
                ->where('reference_type', 'like', '%ProductionOutput%')
                ->orderBy('id')
                ->get(['id', 'reference_id']);

            foreach ($movementRows as $row) {
                DB::table('inventory_movements')
                    ->where('id', $row->id)
                    ->update(['lifecycle_receipt_key' => 'production_output_fg:'.$row->reference_id]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('production_outputs') && Schema::hasColumn('production_outputs', 'posted_job_marker')) {
            Schema::table('production_outputs', function (Blueprint $table) {
                $table->dropUnique('prod_outputs_one_posted_per_job');
                $table->dropColumn('posted_job_marker');
            });
        }

        if (Schema::hasTable('inventory_movements') && Schema::hasColumn('inventory_movements', 'lifecycle_receipt_key')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->dropUnique('inv_movements_lifecycle_receipt_unique');
                $table->dropColumn('lifecycle_receipt_key');
            });
        }
    }
};
