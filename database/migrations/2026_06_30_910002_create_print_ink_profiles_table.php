<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_ink_profiles')) {
            return;
        }

        Schema::create('print_ink_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('ink_type', 40);
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->nullOnDelete();
            $table->decimal('cartridge_cost', 12, 2)->default(0);
            $table->unsignedInteger('estimated_yield_pages')->nullable();
            $table->decimal('estimated_yield_sq_m', 12, 3)->nullable();
            $table->decimal('estimated_ml', 12, 3)->nullable();
            $table->decimal('cost_per_ml', 12, 4)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'ink_type'], 'print_ink_profiles_co_type_idx');
            $table->index(['company_id', 'active'], 'print_ink_profiles_co_active_idx');
        });

        if (
            Schema::hasTable('production_specifications')
            && Schema::hasColumn('production_specifications', 'ink_profile_id')
        ) {
            try {
                Schema::table('production_specifications', function (Blueprint $table) {
                    $table->foreign('ink_profile_id')
                        ->references('id')->on('print_ink_profiles')->nullOnDelete();
                });
            } catch (\Throwable) {
                // FK may already exist.
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('print_ink_profiles');
    }
};
