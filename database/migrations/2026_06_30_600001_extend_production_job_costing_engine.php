<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_cost_sheets', function (Blueprint $table) {
            $table->decimal('estimated_material_cost', 15, 2)->default(0)->after('material_cost');
            $table->decimal('estimated_labor_cost', 15, 2)->default(0)->after('labor_cost');
            $table->decimal('estimated_machine_cost', 15, 2)->default(0)->after('machine_cost');
            $table->decimal('estimated_outsourced_cost', 15, 2)->default(0)->after('outsourced_cost');
            $table->decimal('estimated_overhead_cost', 15, 2)->default(0)->after('overhead_cost');
            $table->decimal('estimated_total_cost', 15, 2)->default(0)->after('total_cost');
            $table->decimal('variance_amount', 15, 2)->default(0)->after('estimated_total_cost');
            $table->decimal('variance_percent', 8, 2)->default(0)->after('variance_amount');
            $table->string('cost_review_status', 30)->default('none')->after('status');
            $table->foreignId('cost_approved_by')->nullable()->after('cost_review_status')->constrained('users')->nullOnDelete();
            $table->timestamp('cost_approved_at')->nullable()->after('cost_approved_by');
            $table->boolean('is_frozen')->default(false)->after('cost_approved_at');
            $table->index(['company_id', 'cost_review_status'], 'job_cost_sheets_review_idx');
            $table->index(['company_id', 'created_at'], 'job_cost_sheets_company_created_idx');
        });

        Schema::table('job_cost_lines', function (Blueprint $table) {
            $table->string('component_type', 30)->nullable()->after('cost_category');
            $table->foreignId('employee_id')->nullable()->after('inventory_movement_id')->constrained()->nullOnDelete();
            $table->foreignId('machine_profile_id')->nullable()->after('employee_id')->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->after('machine_profile_id')->constrained()->nullOnDelete();
            $table->string('vendor_name')->nullable()->after('purchase_order_id');
            $table->string('reference_number')->nullable()->after('vendor_name');
            $table->decimal('hours', 10, 2)->nullable()->after('quantity');
            $table->decimal('hourly_rate', 15, 2)->nullable()->after('hours');
            $table->boolean('is_system_generated')->default(true)->after('line_total');
            $table->index('component_type', 'job_cost_lines_component_type_idx');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('estimated_material_cost', 15, 2)->default(0)->after('total_amount');
            $table->decimal('estimated_labor_cost', 15, 2)->default(0)->after('estimated_material_cost');
            $table->decimal('estimated_machine_cost', 15, 2)->default(0)->after('estimated_labor_cost');
            $table->decimal('estimated_outsourcing_cost', 15, 2)->default(0)->after('estimated_machine_cost');
            $table->decimal('estimated_overhead_cost', 15, 2)->default(0)->after('estimated_outsourcing_cost');
            $table->decimal('estimated_total_cost', 15, 2)->default(0)->after('estimated_overhead_cost');
        });

        Schema::create('job_cost_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_cost_sheet_id')->constrained()->cascadeOnDelete();
            $table->string('snapshot_reason', 30)->default('job_closed');
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('material_cost', 15, 2)->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('machine_cost', 15, 2)->default(0);
            $table->decimal('outsourced_cost', 15, 2)->default(0);
            $table->decimal('overhead_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('gross_margin_percent', 8, 2)->default(0);
            $table->timestamp('snapshot_at');
            $table->timestamps();

            $table->index(['production_job_card_id', 'snapshot_at'], 'job_cost_snapshots_job_idx');
            $table->index(['company_id', 'created_at'], 'job_cost_snapshots_company_created_idx');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('job_cost_snapshots');

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'estimated_material_cost', 'estimated_labor_cost', 'estimated_machine_cost',
                'estimated_outsourcing_cost', 'estimated_overhead_cost', 'estimated_total_cost',
            ]);
        });

        Schema::table('job_cost_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
            $table->dropConstrainedForeignId('machine_profile_id');
            $table->dropConstrainedForeignId('purchase_order_id');
            $table->dropColumn([
                'component_type', 'vendor_name', 'reference_number', 'hours', 'hourly_rate', 'is_system_generated',
            ]);
        });

        Schema::table('job_cost_sheets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cost_approved_by');
            $table->dropIndex('job_cost_sheets_review_idx');
            $table->dropIndex('job_cost_sheets_company_created_idx');
            $table->dropColumn([
                'estimated_material_cost', 'estimated_labor_cost', 'estimated_machine_cost',
                'estimated_outsourced_cost', 'estimated_overhead_cost', 'estimated_total_cost',
                'variance_amount', 'variance_percent', 'cost_review_status', 'cost_approved_at', 'is_frozen',
            ]);
        });
    }

};
