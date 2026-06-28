<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_bom_lines') && ! Schema::hasColumn('product_bom_lines', 'quantity_formula')) {
            Schema::table('product_bom_lines', function (Blueprint $table) {
                $table->string('quantity_formula', 120)->nullable()->after('quantity_per_unit');
                $table->boolean('is_active')->default(true)->after('sort_order');
            });
        }

        if (Schema::hasTable('production_material_requirements')) {
            Schema::table('production_material_requirements', function (Blueprint $table) {
                if (! Schema::hasColumn('production_material_requirements', 'issued_quantity')) {
                    $table->decimal('issued_quantity', 12, 3)->default(0)->after('consumed_quantity');
                }
                if (! Schema::hasColumn('production_material_requirements', 'waste_quantity')) {
                    $table->decimal('waste_quantity', 12, 3)->default(0)->after('issued_quantity');
                }
                if (! Schema::hasColumn('production_material_requirements', 'returned_quantity')) {
                    $table->decimal('returned_quantity', 12, 3)->default(0)->after('waste_quantity');
                }
                if (! Schema::hasColumn('production_material_requirements', 'quantity_formula')) {
                    $table->string('quantity_formula', 120)->nullable()->after('job_quantity');
                }
            });

            if (! $this->indexExists('production_material_requirements', 'pmr_job_item_idx')) {
                Schema::table('production_material_requirements', function (Blueprint $table) {
                    $table->index(['production_job_card_id', 'inventory_item_id'], 'pmr_job_item_idx');
                });
            }
        }

        if (Schema::hasTable('production_material_requirements') && ! Schema::hasTable('production_material_issues')) {
            Schema::create('production_material_issues', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('production_material_requirement_id')->nullable();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->foreignId('inventory_movement_id')->constrained()->cascadeOnDelete();
                $table->decimal('quantity', 12, 3);
                $table->decimal('unit_cost', 15, 2)->default(0);
                $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('issued_at');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('production_material_requirement_id', 'pmi_pmr_fk')
                    ->references('id')->on('production_material_requirements')->nullOnDelete();
                $table->index(['production_job_card_id', 'inventory_item_id'], 'pmi_job_item_idx');
                $table->index(['company_id', 'branch_id', 'issued_at'], 'pmi_tenant_issued_idx');
            });
        }

        if (Schema::hasTable('production_material_requirements') && ! Schema::hasTable('production_session_materials')) {
            Schema::create('production_session_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('production_session_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('production_material_requirement_id')->nullable();
                $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->decimal('consumed_quantity', 12, 3)->default(0);
                $table->decimal('waste_quantity', 12, 3)->default(0);
                $table->decimal('returned_quantity', 12, 3)->default(0);
                $table->timestamps();

                $table->foreign('production_material_requirement_id', 'psm_pmr_fk')
                    ->references('id')->on('production_material_requirements')->nullOnDelete();
                $table->index(['production_session_id', 'inventory_item_id'], 'psm_session_item_idx');
            });
        }

        if (Schema::hasTable('inventory_movements') && ! $this->indexExists('inventory_movements', 'inv_mov_type_ref_idx')) {
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->index(['movement_type', 'reference_type', 'reference_id'], 'inv_mov_type_ref_idx');
            });
        }

        if (Schema::hasTable('production_material_consumptions')
            && ! $this->indexExists('production_material_consumptions', 'pmc_job_item_idx')) {
            Schema::table('production_material_consumptions', function (Blueprint $table) {
                $table->index(['production_job_card_id', 'inventory_item_id'], 'pmc_job_item_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_session_materials');
        Schema::dropIfExists('production_material_issues');

        if (Schema::hasTable('production_material_requirements')) {
            Schema::table('production_material_requirements', function (Blueprint $table) {
                if ($this->indexExists('production_material_requirements', 'pmr_job_item_idx')) {
                    $table->dropIndex('pmr_job_item_idx');
                }
                foreach (['issued_quantity', 'waste_quantity', 'returned_quantity', 'quantity_formula'] as $col) {
                    if (Schema::hasColumn('production_material_requirements', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasColumn('product_bom_lines', 'quantity_formula')) {
            Schema::table('product_bom_lines', function (Blueprint $table) {
                $table->dropColumn(['quantity_formula', 'is_active']);
            });
        }
    }

    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($row) => ($row->name ?? '') === $index);
        }

        $database = $connection->getDatabaseName();

        return (bool) $connection->selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index],
        );
    }
};
