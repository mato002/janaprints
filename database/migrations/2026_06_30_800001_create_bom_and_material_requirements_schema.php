<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_boms')) {
            Schema::create('product_boms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finished_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'finished_item_id'], 'product_boms_finished_unique');
            $table->index(['company_id', 'branch_id', 'is_active']);
            });
        }

        if (! Schema::hasTable('product_bom_lines')) {
            Schema::create('product_bom_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_bom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_per_unit', 12, 4);
            $table->string('quantity_formula', 120)->nullable();
            $table->decimal('waste_factor_percent', 5, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_bom_id', 'inventory_item_id'], 'product_bom_lines_item_unique');
            });
        }

        if (Schema::hasTable('sales_order_items') && ! Schema::hasColumn('sales_order_items', 'inventory_item_id')) {
            Schema::table('sales_order_items', function (Blueprint $table) {
                $table->foreignId('inventory_item_id')
                    ->nullable()
                    ->after('sales_order_id')
                    ->constrained('inventory_items')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('production_material_requirements')) {
            Schema::create('production_material_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_bom_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('finished_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('sales_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('job_quantity', 12, 3)->default(1);
            $table->string('quantity_formula', 120)->nullable();
            $table->decimal('required_quantity', 12, 3);
            $table->decimal('reserved_quantity', 12, 3)->default(0);
            $table->decimal('consumed_quantity', 12, 3)->default(0);
            $table->decimal('issued_quantity', 12, 3)->default(0);
            $table->decimal('waste_quantity', 12, 3)->default(0);
            $table->decimal('returned_quantity', 12, 3)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->string('status', 20)->default('planned');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['production_job_card_id', 'status'], 'pmr_job_status_idx');
            $table->index(['production_job_card_id', 'inventory_item_id'], 'pmr_job_item_idx');
            $table->index(['company_id', 'branch_id', 'inventory_item_id', 'warehouse_id'], 'pmr_item_wh_idx');
            });
        }

        if (Schema::hasTable('production_material_consumptions')
            && ! Schema::hasColumn('production_material_consumptions', 'production_material_requirement_id')) {
            Schema::table('production_material_consumptions', function (Blueprint $table) {
                $table->foreignId('production_material_requirement_id')
                    ->nullable()
                    ->after('production_job_card_id')
                    ->constrained('production_material_requirements', 'id', 'pmc_pmr_id_foreign')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('production_material_consumptions', 'production_material_requirement_id')) {
            Schema::table('production_material_consumptions', function (Blueprint $table) {
                $table->dropForeign('pmc_pmr_id_foreign');
                $table->dropColumn('production_material_requirement_id');
            });
        }

        Schema::dropIfExists('production_material_requirements');
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inventory_item_id');
        });
        Schema::dropIfExists('product_bom_lines');
        Schema::dropIfExists('product_boms');
    }
};
