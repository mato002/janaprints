<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('production_outputs')) {
            return;
        }

        Schema::create('production_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finished_inventory_item_id')->constrained('inventory_items')->restrictOnDelete();
            $table->foreignId('finished_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('inventory_movement_id')->nullable()->constrained('inventory_movements')->nullOnDelete();
            $table->decimal('quantity_completed', 14, 3);
            $table->decimal('quantity_rejected', 14, 3)->default(0);
            $table->decimal('unit_cost', 14, 4)->nullable();
            $table->decimal('total_cost', 14, 2)->nullable();
            $table->string('completion_status', 20)->default('draft');
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('posted_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'completion_status'], 'prod_outputs_co_status_idx');
            $table->index('production_job_card_id', 'prod_outputs_job_idx');
            $table->index('finished_inventory_item_id', 'prod_outputs_item_idx');
            $table->index('posted_journal_id', 'prod_outputs_journal_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_outputs');
    }
};
