<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_reorder_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_quantity', 12, 3)->default(0);
            $table->decimal('reorder_level', 12, 3)->default(0);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('alerted_at')->useCurrent();
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'inventory_item_id'], 'inv_reorder_alerts_tenant_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_reorder_alerts');
    }
};
