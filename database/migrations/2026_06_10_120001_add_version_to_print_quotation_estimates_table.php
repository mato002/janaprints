<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('print_quotation_estimates')) {
            return;
        }

        Schema::table('print_quotation_estimates', function (Blueprint $table) {
            if (! Schema::hasColumn('print_quotation_estimates', 'version')) {
                $table->unsignedSmallInteger('version')->default(1)->after('quantity');
            }
        });

        Schema::table('print_quotation_estimates', function (Blueprint $table) {
            if (Schema::hasColumn('print_quotation_estimates', 'version')) {
                try {
                    $table->dropUnique('pq_est_analysis_qty_material_uq');
                } catch (\Throwable) {
                    // Index may already be replaced.
                }

                $table->unique(
                    ['print_artwork_analysis_id', 'quantity', 'material_inventory_item_id', 'version'],
                    'pq_est_analysis_qty_material_ver_uq',
                );
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('print_quotation_estimates')) {
            return;
        }

        Schema::table('print_quotation_estimates', function (Blueprint $table) {
            try {
                $table->dropUnique('pq_est_analysis_qty_material_ver_uq');
            } catch (\Throwable) {
                //
            }

            $table->unique(
                ['print_artwork_analysis_id', 'quantity', 'material_inventory_item_id'],
                'pq_est_analysis_qty_material_uq',
            );

            if (Schema::hasColumn('print_quotation_estimates', 'version')) {
                $table->dropColumn('version');
            }
        });
    }
};
