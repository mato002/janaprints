<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->foreignId('default_uom_id')->nullable()->after('description')->constrained('units_of_measure')->nullOnDelete();
            $table->string('reorder_behavior')->default('standard')->after('default_uom_id');
            $table->index(['company_id', 'branch_id', 'is_active']);
        });

        Schema::create('inventory_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_category_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'inventory_category_id', 'code'], 'inv_subcat_tenant_category_code_unique');
            $table->index(['company_id', 'branch_id', 'inventory_category_id'], 'inv_subcat_tenant_category_index');
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'code']);
            $table->index(['company_id', 'branch_id', 'is_active']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('subcategory_id')->nullable()->after('inventory_category_id')->constrained('inventory_subcategories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('subcategory_id')->constrained('brands')->nullOnDelete();
            $table->string('item_code')->nullable()->after('sku');
            $table->index(['inventory_category_id']);
            $table->index(['subcategory_id']);
            $table->index(['brand_id']);
            $table->index(['sku']);
            $table->index(['item_code']);
        });

        Schema::create('item_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('data_type')->default('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'code']);
            $table->index(['inventory_category_id']);
        });

        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['item_attribute_id', 'value']);
        });

        Schema::create('inventory_item_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_option_id')->nullable()->constrained()->nullOnDelete();
            $table->string('value')->nullable();
            $table->timestamps();

            $table->unique(['inventory_item_id', 'item_attribute_id'], 'inv_item_attr_item_attribute_unique');
        });

        Schema::create('inventory_item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['inventory_item_id', 'is_primary']);
        });

        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('currency', 3)->default('KES');
            $table->date('effective_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'name']);
            $table->index(['company_id', 'branch_id', 'status']);
        });

        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_override', 15, 2);
            $table->timestamps();

            $table->unique(['price_list_id', 'inventory_item_id']);
            $table->index(['inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('inventory_item_images');
        Schema::dropIfExists('inventory_item_attributes');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('item_attributes');

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('subcategory_id');
            $table->dropColumn('item_code');
        });

        Schema::dropIfExists('brands');
        Schema::dropIfExists('inventory_subcategories');

        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_uom_id');
            $table->dropColumn('reorder_behavior');
        });
    }
};
