<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->string('asset_type', 30)->default('other')->after('code');
            $table->unsignedSmallInteger('useful_life_years')->nullable()->after('useful_life_months');
            $table->text('description')->nullable()->after('depreciation_method');
            $table->timestamp('archived_at')->nullable()->after('is_active');
            $table->index(['company_id', 'asset_type'], 'asset_categories_company_type_idx');
        });

        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->string('manufacturer', 120)->nullable()->after('serial_number');
            $table->string('model', 120)->nullable()->after('manufacturer');
            $table->timestamp('archived_at')->nullable()->after('notes');
            $table->index(['company_id', 'branch_id'], 'fixed_assets_company_branch_idx');
            $table->index(['company_id', 'asset_category_id'], 'fixed_assets_company_category_idx');
            $table->index(['company_id', 'assigned_to_user_id'], 'fixed_assets_company_assignee_idx');
            $table->index(['company_id', 'created_at'], 'fixed_assets_company_created_idx');
        });

        DB::table('fixed_assets')
            ->where('status', 'under_repair')
            ->update(['status' => 'under_maintenance']);

        DB::table('asset_categories')
            ->whereNull('useful_life_years')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $category): void {
                DB::table('asset_categories')
                    ->where('id', $category->id)
                    ->update([
                        'useful_life_years' => max(1, (int) ceil(((int) ($category->useful_life_months ?? 12)) / 12)),
                    ]);
            });

        Schema::create('asset_assignment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('assignment_type', 20);
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['fixed_asset_id', 'assigned_at'], 'asset_assignment_histories_asset_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignment_histories');

        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropIndex('fixed_assets_company_branch_idx');
            $table->dropIndex('fixed_assets_company_category_idx');
            $table->dropIndex('fixed_assets_company_assignee_idx');
            $table->dropIndex('fixed_assets_company_created_idx');
            $table->dropColumn(['manufacturer', 'model', 'archived_at']);
        });

        DB::table('fixed_assets')
            ->where('status', 'under_maintenance')
            ->update(['status' => 'under_repair']);

        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropIndex('asset_categories_company_type_idx');
            $table->dropColumn(['asset_type', 'useful_life_years', 'description', 'archived_at']);
        });
    }
};
