<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotations')) {
            return;
        }

        Schema::table('quotations', function (Blueprint $table) {
            if (! Schema::hasColumn('quotations', 'estimated_material_cost')) {
                $table->decimal('estimated_material_cost', 14, 2)->nullable()->after('total_amount');
            }
            if (! Schema::hasColumn('quotations', 'estimated_ink_cost')) {
                $table->decimal('estimated_ink_cost', 14, 2)->nullable()->after('estimated_material_cost');
            }
            if (! Schema::hasColumn('quotations', 'estimated_machine_cost')) {
                $table->decimal('estimated_machine_cost', 14, 2)->nullable()->after('estimated_ink_cost');
            }
            if (! Schema::hasColumn('quotations', 'estimated_labour_cost')) {
                $table->decimal('estimated_labour_cost', 14, 2)->nullable()->after('estimated_machine_cost');
            }
            if (! Schema::hasColumn('quotations', 'estimated_overhead_cost')) {
                $table->decimal('estimated_overhead_cost', 14, 2)->nullable()->after('estimated_labour_cost');
            }
            if (! Schema::hasColumn('quotations', 'estimated_total_cost')) {
                $table->decimal('estimated_total_cost', 14, 2)->nullable()->after('estimated_overhead_cost');
            }
            if (! Schema::hasColumn('quotations', 'estimated_margin_percent')) {
                $table->decimal('estimated_margin_percent', 6, 2)->nullable()->after('estimated_total_cost');
            }
            if (! Schema::hasColumn('quotations', 'recommended_price')) {
                $table->decimal('recommended_price', 14, 2)->nullable()->after('estimated_margin_percent');
            }
            if (! Schema::hasColumn('quotations', 'confidence_score')) {
                $table->decimal('confidence_score', 5, 2)->nullable()->after('recommended_price');
            }
            if (! Schema::hasColumn('quotations', 'estimation_version')) {
                $table->string('estimation_version', 40)->nullable()->after('confidence_score');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('quotations')) {
            return;
        }

        Schema::table('quotations', function (Blueprint $table) {
            foreach ([
                'estimated_material_cost',
                'estimated_ink_cost',
                'estimated_machine_cost',
                'estimated_labour_cost',
                'estimated_overhead_cost',
                'estimated_total_cost',
                'estimated_margin_percent',
                'recommended_price',
                'confidence_score',
                'estimation_version',
            ] as $column) {
                if (Schema::hasColumn('quotations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
