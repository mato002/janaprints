<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('machine_profiles')) {
            return;
        }

        Schema::table('machine_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('machine_profiles', 'cost_per_hour')) {
                $table->decimal('cost_per_hour', 12, 2)->nullable()->after('current_utilization');
            }
            if (! Schema::hasColumn('machine_profiles', 'power_rating_kw')) {
                $table->decimal('power_rating_kw', 8, 2)->nullable()->after('cost_per_hour');
            }
            if (! Schema::hasColumn('machine_profiles', 'average_setup_minutes')) {
                $table->unsignedInteger('average_setup_minutes')->nullable()->after('power_rating_kw');
            }
            if (! Schema::hasColumn('machine_profiles', 'maintenance_cost_factor')) {
                $table->decimal('maintenance_cost_factor', 6, 3)->nullable()->after('average_setup_minutes');
            }
            if (! Schema::hasColumn('machine_profiles', 'target_output_per_hour')) {
                $table->decimal('target_output_per_hour', 12, 2)->nullable()->after('maintenance_cost_factor');
            }
            if (! Schema::hasColumn('machine_profiles', 'cost_notes')) {
                $table->text('cost_notes')->nullable()->after('target_output_per_hour');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('machine_profiles')) {
            return;
        }

        Schema::table('machine_profiles', function (Blueprint $table) {
            foreach ([
                'cost_per_hour',
                'power_rating_kw',
                'average_setup_minutes',
                'maintenance_cost_factor',
                'target_output_per_hour',
                'cost_notes',
            ] as $column) {
                if (Schema::hasColumn('machine_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
