<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('print_product_templates')) {
            Schema::create('print_product_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->string('code', 40);
                $table->string('name', 120);
                $table->string('category', 40);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('revision_number')->default(1);
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->json('metadata')->nullable();

                $table->string('production_type', 40)->nullable();
                $table->unsignedBigInteger('default_paper_inventory_item_id')->nullable();
                $table->foreign('default_paper_inventory_item_id', 'ppt_default_paper_fk')->references('id')->on('inventory_items')->nullOnDelete();
                $table->unsignedBigInteger('default_material_inventory_item_id')->nullable();
                $table->foreign('default_material_inventory_item_id', 'ppt_default_material_fk')->references('id')->on('inventory_items')->nullOnDelete();
                $table->string('gsm', 40)->nullable();
                $table->string('default_size', 80)->nullable();
                $table->string('default_finished_size', 80)->nullable();
                $table->string('default_sheet_size', 80)->nullable();
                $table->string('default_orientation', 20)->nullable();
                $table->string('default_colour_mode', 40)->nullable();
                $table->unsignedTinyInteger('number_of_colours')->nullable();
                $table->string('default_sides', 20)->nullable();
                $table->string('default_binding_type', 60)->nullable();
                $table->string('default_finishing_type', 60)->nullable();
                $table->boolean('default_lamination')->default(false);
                $table->boolean('default_foiling')->default(false);
                $table->boolean('default_spot_uv')->default(false);
                $table->boolean('default_embossing')->default(false);
                $table->boolean('default_debossing')->default(false);
                $table->boolean('default_die_cutting')->default(false);
                $table->boolean('default_creasing')->default(false);
                $table->boolean('default_perforation')->default(false);
                $table->boolean('default_numbering_required')->default(false);
                $table->boolean('default_eyelets')->default(false);
                $table->decimal('default_waste_allowance_percent', 5, 2)->nullable();
                $table->unsignedSmallInteger('default_ups')->nullable();
                $table->text('default_notes')->nullable();

                $table->boolean('artwork_required')->default(true);
                $table->boolean('bleed_required')->default(false);
                $table->string('safe_margin', 40)->nullable();
                $table->string('resolution_recommendation', 80)->nullable();

                $table->foreignId('preferred_work_center_id')->nullable()->constrained('work_centers')->nullOnDelete();
                $table->unsignedBigInteger('preferred_machine_asset_id')->nullable();
                $table->foreign('preferred_machine_asset_id', 'ppt_pref_machine_fk')->references('id')->on('fixed_assets')->nullOnDelete();
                $table->string('preferred_operator_skill', 80)->nullable();
                $table->boolean('optional_outsource')->default(false);
                $table->unsignedBigInteger('recommended_qc_checklist_id')->nullable();
                $table->foreign('recommended_qc_checklist_id', 'ppt_qc_checklist_fk')->references('id')->on('product_qc_checklists')->nullOnDelete();
                $table->text('recommended_packaging')->nullable();

                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['company_id', 'code'], 'ppt_company_code_unique');
                $table->index(['company_id', 'branch_id', 'is_active', 'category'], 'ppt_tenant_active_idx');
            });
        }

        if (Schema::hasTable('production_specifications') && ! Schema::hasColumn('production_specifications', 'print_product_template_id')) {
            Schema::table('production_specifications', function (Blueprint $table) {
                $table->foreignId('print_product_template_id')
                    ->nullable()
                    ->after('quotation_item_id')
                    ->constrained('print_product_templates', indexName: 'prod_spec_template_fk')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('production_specifications') && Schema::hasColumn('production_specifications', 'print_product_template_id')) {
            Schema::table('production_specifications', function (Blueprint $table) {
                $table->dropConstrainedForeignId('print_product_template_id');
            });
        }

        Schema::dropIfExists('print_product_templates');
    }
};
