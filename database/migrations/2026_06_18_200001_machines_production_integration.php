<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('machine_code', 50);
            $table->string('machine_type', 50);
            $table->string('manufacturer', 120)->nullable();
            $table->string('model', 120)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('production_area', 120)->nullable();
            $table->date('installation_date')->nullable();
            $table->string('capacity_unit', 30)->default('jobs');
            $table->decimal('capacity_per_hour', 12, 2)->default(0);
            $table->decimal('capacity_per_shift', 12, 2)->default(0);
            $table->boolean('is_primary_production_machine')->default(false);
            $table->string('production_status', 30)->default('available');
            $table->decimal('hourly_capacity', 12, 2)->default(0);
            $table->decimal('daily_capacity', 12, 2)->default(0);
            $table->decimal('shift_capacity', 12, 2)->default(0);
            $table->decimal('monthly_capacity', 12, 2)->default(0);
            $table->decimal('current_utilization', 5, 2)->default(0);
            $table->timestamps();

            $table->unique('fixed_asset_id');
            $table->unique(['company_id', 'machine_code']);
            $table->index(['company_id', 'production_status'], 'machine_profiles_company_status_idx');
            $table->index(['company_id', 'branch_id'], 'machine_profiles_company_branch_idx');
            $table->index(['company_id', 'machine_type'], 'machine_profiles_company_type_idx');
        });

        Schema::table('work_centers', function (Blueprint $table) {
            $table->foreignId('fixed_asset_id')->nullable()->after('branch_id')->constrained('fixed_assets')->nullOnDelete();
            $table->index('fixed_asset_id', 'work_centers_fixed_asset_idx');
        });

        Schema::table('production_job_cards', function (Blueprint $table) {
            $table->foreignId('assigned_machine_asset_id')->nullable()->after('created_by')->constrained('fixed_assets')->nullOnDelete();
            $table->index('assigned_machine_asset_id', 'production_job_cards_machine_idx');
        });

        Schema::create('machine_timeline_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['fixed_asset_id', 'occurred_at'], 'machine_timeline_asset_idx');
        });

        Schema::create('machine_job_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_job_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['fixed_asset_id', 'assigned_at'], 'machine_job_assignments_asset_idx');
            $table->index(['production_job_card_id', 'assigned_at'], 'machine_job_assignments_job_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machine_job_assignments');
        Schema::dropIfExists('machine_timeline_entries');

        Schema::table('production_job_cards', function (Blueprint $table) {
            $table->dropIndex('production_job_cards_machine_idx');
            $table->dropConstrainedForeignId('assigned_machine_asset_id');
        });

        Schema::table('work_centers', function (Blueprint $table) {
            $table->dropIndex('work_centers_fixed_asset_idx');
            $table->dropConstrainedForeignId('fixed_asset_id');
        });

        Schema::dropIfExists('machine_profiles');
    }
};
