<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('production_specifications')) {
            return;
        }

        Schema::create('production_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sales_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('production_job_card_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_item_id')->nullable()->constrained('quotation_items')->nullOnDelete();

            $table->string('production_type', 40)->nullable();
            $table->string('product_description', 500)->nullable();
            $table->decimal('quantity', 12, 3)->nullable();
            $table->string('unit', 40)->nullable();
            $table->string('size', 80)->nullable();
            $table->string('finished_size', 80)->nullable();
            $table->string('sheet_size', 80)->nullable();
            $table->string('orientation', 20)->nullable();
            $table->foreignId('paper_inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->foreignId('material_inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->string('ink_type', 40)->nullable();
            $table->foreignId('ink_profile_id')->nullable()->constrained('print_ink_profiles')->nullOnDelete();
            $table->string('colour_mode', 40)->nullable();
            $table->string('sides', 20)->nullable();
            $table->string('binding_type', 60)->nullable();
            $table->string('finishing_type', 60)->nullable();
            $table->boolean('lamination')->default(false);
            $table->boolean('foiling')->default(false);
            $table->boolean('spot_uv')->default(false);
            $table->boolean('embossing')->default(false);
            $table->boolean('debossing')->default(false);
            $table->boolean('die_cutting')->default(false);
            $table->boolean('creasing')->default(false);
            $table->boolean('perforation')->default(false);
            $table->boolean('numbering_required')->default(false);
            $table->boolean('eyelets')->default(false);
            $table->unsignedSmallInteger('ups')->nullable();
            $table->unsignedInteger('estimated_sheets')->nullable();
            $table->decimal('waste_allowance_percent', 5, 2)->nullable();
            $table->string('artwork_reference', 120)->nullable();
            $table->string('artwork_version', 40)->nullable();
            $table->text('production_notes')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('approval_status', 30)->default('draft');
            $table->json('snapshot_payload')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('sales_order_item_id', 'prod_spec_so_item_unique');
            $table->unique('production_job_card_id', 'prod_spec_job_card_unique');
            $table->index(['company_id', 'branch_id', 'production_type'], 'prod_spec_tenant_type_idx');
            $table->index(['customer_id', 'created_at'], 'prod_spec_customer_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_specifications');
    }
};
