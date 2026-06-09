<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('production_wastage_records')) {
            Schema::create('production_wastage_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->string('flow_type', 20);
                $table->string('waste_type', 30)->nullable();
                $table->string('custom_reason')->nullable();
                $table->decimal('quantity', 12, 3);
                $table->decimal('unit_cost', 15, 2)->default(0);
                $table->decimal('line_cost', 15, 2)->default(0);
                $table->foreignId('inventory_movement_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('machine_profile_id')->nullable()->constrained('machine_profiles')->nullOnDelete();
                $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('recorded_at');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['production_job_card_id', 'flow_type'], 'pwr_job_flow_idx');
                $table->index(['company_id', 'branch_id', 'recorded_at'], 'pwr_tenant_date_idx');
            });
        }

        if (Schema::hasTable('job_cost_sheets')) {
            Schema::table('job_cost_sheets', function (Blueprint $table) {
                if (! Schema::hasColumn('job_cost_sheets', 'wastage_cost')) {
                    $table->decimal('wastage_cost', 15, 2)->default(0)->after('material_cost');
                }
                if (! Schema::hasColumn('job_cost_sheets', 'material_issued_qty')) {
                    $table->decimal('material_issued_qty', 12, 3)->default(0)->after('wastage_cost');
                }
                if (! Schema::hasColumn('job_cost_sheets', 'material_consumed_qty')) {
                    $table->decimal('material_consumed_qty', 12, 3)->default(0)->after('material_issued_qty');
                }
                if (! Schema::hasColumn('job_cost_sheets', 'material_wasted_qty')) {
                    $table->decimal('material_wasted_qty', 12, 3)->default(0)->after('material_consumed_qty');
                }
                if (! Schema::hasColumn('job_cost_sheets', 'material_returned_qty')) {
                    $table->decimal('material_returned_qty', 12, 3)->default(0)->after('material_wasted_qty');
                }
                if (! Schema::hasColumn('job_cost_sheets', 'waste_percent')) {
                    $table->decimal('waste_percent', 8, 2)->default(0)->after('material_returned_qty');
                }
                if (! Schema::hasColumn('job_cost_sheets', 'yield_percent')) {
                    $table->decimal('yield_percent', 8, 2)->default(0)->after('waste_percent');
                }
                if (! Schema::hasColumn('job_cost_sheets', 'material_efficiency_percent')) {
                    $table->decimal('material_efficiency_percent', 8, 2)->default(0)->after('yield_percent');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('job_cost_sheets')) {
            Schema::table('job_cost_sheets', function (Blueprint $table) {
                foreach ([
                    'wastage_cost', 'material_issued_qty', 'material_consumed_qty',
                    'material_wasted_qty', 'material_returned_qty',
                    'waste_percent', 'yield_percent', 'material_efficiency_percent',
                ] as $column) {
                    if (Schema::hasColumn('job_cost_sheets', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('production_wastage_records');
    }
};
