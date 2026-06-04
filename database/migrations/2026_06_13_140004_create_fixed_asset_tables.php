<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedInteger('useful_life_months')->default(60);
            $table->decimal('depreciation_rate_percent', 8, 2)->nullable();
            $table->string('depreciation_method', 30)->default('straight_line');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_category_id')->constrained()->cascadeOnDelete();
            $table->string('asset_number');
            $table->string('asset_name');
            $table->string('barcode')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2);
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->string('status', 30)->default('active');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'asset_number']);
            $table->index(['company_id', 'status'], 'fixed_assets_company_status_idx');
            $table->index('asset_category_id');
        });

        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->foreignId('transferred_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('fixed_asset_id');
        });

        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('maintenance_type', 30);
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->decimal('cost', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['fixed_asset_id', 'status'], 'asset_maintenances_asset_status_idx');
        });

        Schema::create('asset_depreciation_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->date('period_date');
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated_after', 15, 2);
            $table->decimal('net_book_value_after', 15, 2);
            $table->timestamps();

            $table->unique(['fixed_asset_id', 'period_date']);
        });

        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->date('disposal_date');
            $table->decimal('disposal_proceeds', 15, 2)->default(0);
            $table->decimal('gain_loss_amount', 15, 2)->default(0);
            $table->string('disposal_method', 30)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('disposed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
        Schema::dropIfExists('asset_depreciation_entries');
        Schema::dropIfExists('asset_maintenances');
        Schema::dropIfExists('asset_transfers');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('asset_categories');
    }
};
