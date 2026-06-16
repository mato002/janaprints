<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_velocity_snapshots')) {
            return;
        }

        Schema::create('inventory_velocity_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stock_role', 30)->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedSmallInteger('movement_window_days');
            $table->decimal('opening_balance', 14, 3)->nullable();
            $table->decimal('closing_balance', 14, 3)->nullable();
            $table->decimal('total_in_quantity', 14, 3)->default(0);
            $table->decimal('total_out_quantity', 14, 3)->default(0);
            $table->decimal('net_quantity', 14, 3)->default(0);
            $table->decimal('average_daily_consumption', 14, 4)->default(0);
            $table->decimal('average_weekly_consumption', 14, 4)->default(0);
            $table->decimal('days_to_depletion', 10, 2)->nullable();
            $table->string('velocity_class', 30);
            $table->string('risk_level', 20);
            $table->timestamp('last_inbound_at')->nullable();
            $table->timestamp('last_outbound_at')->nullable();
            $table->timestamp('last_movement_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->index('company_id', 'inv_velocity_co_idx');
            $table->index('inventory_item_id', 'inv_velocity_item_idx');
            $table->index('warehouse_id', 'inv_velocity_wh_idx');
            $table->index('velocity_class', 'inv_velocity_class_idx');
            $table->index('risk_level', 'inv_velocity_risk_idx');
            $table->index('period_end', 'inv_velocity_period_end_idx');

            $table->unique(
                ['company_id', 'inventory_item_id', 'warehouse_id', 'period_end', 'movement_window_days'],
                'inv_velocity_snapshot_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_velocity_snapshots');
    }
};
