<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_technicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('technician_type', 20)->default('internal');
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('specialization', 120)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['company_id', 'status'], 'maintenance_technicians_company_status_idx');
            $table->index(['company_id', 'branch_id'], 'maintenance_technicians_company_branch_idx');
        });

        Schema::create('maintenance_work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('work_order_no', 50);
            $table->string('maintenance_type', 30);
            $table->string('priority', 20)->default('normal');
            $table->string('status', 30)->default('draft');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('maintenance_technicians')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('maintenance_plan_id')->nullable();
            $table->text('description')->nullable();
            $table->text('findings')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'work_order_no'], 'maintenance_work_orders_company_no_idx');
            $table->index(['company_id', 'status'], 'maintenance_work_orders_company_status_idx');
            $table->index(['company_id', 'priority'], 'maintenance_work_orders_company_priority_idx');
            $table->index(['company_id', 'branch_id'], 'maintenance_work_orders_company_branch_idx');
            $table->index(['fixed_asset_id', 'status'], 'maintenance_work_orders_asset_status_idx');
            $table->index(['scheduled_for'], 'maintenance_work_orders_scheduled_idx');
        });

        Schema::create('maintenance_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('plan_name');
            $table->string('frequency_type', 30);
            $table->unsignedInteger('frequency_value')->default(1);
            $table->date('next_due_date')->nullable();
            $table->date('last_completed_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'is_active'], 'maintenance_plans_company_active_idx');
            $table->index(['company_id', 'next_due_date'], 'maintenance_plans_company_due_idx');
            $table->index(['fixed_asset_id', 'is_active'], 'maintenance_plans_asset_active_idx');
        });

        Schema::table('maintenance_work_orders', function (Blueprint $table) {
            $table->foreign('maintenance_plan_id')->references('id')->on('maintenance_plans')->nullOnDelete();
        });

        Schema::create('maintenance_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('incident_no', 50);
            $table->string('severity', 20)->default('normal');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('reported_at');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'incident_no'], 'maintenance_incidents_company_no_idx');
            $table->index(['fixed_asset_id', 'reported_at'], 'maintenance_incidents_asset_idx');
        });

        Schema::create('asset_downtime_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->string('reason')->nullable();
            $table->string('impact_level', 20)->default('medium');
            $table->timestamps();

            $table->index(['fixed_asset_id', 'start_time'], 'asset_downtime_asset_start_idx');
            $table->index(['company_id', 'impact_level'], 'asset_downtime_company_impact_idx');
            $table->index(['company_id', 'branch_id'], 'asset_downtime_company_branch_idx');
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('log_type', 30)->default('service');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['fixed_asset_id', 'logged_at'], 'maintenance_logs_asset_idx');
            $table->index(['maintenance_work_order_id'], 'maintenance_logs_work_order_idx');
        });

        Schema::create('maintenance_timeline_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['fixed_asset_id', 'occurred_at'], 'maintenance_timeline_asset_idx');
        });

        Schema::create('maintenance_work_order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintenance_work_order_id');
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->foreign('maintenance_work_order_id', 'mwo_status_hist_order_fk')
                ->references('id')->on('maintenance_work_orders')->cascadeOnDelete();
            $table->index(['maintenance_work_order_id', 'changed_at'], 'mwo_status_histories_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_work_order_status_histories');
        Schema::dropIfExists('maintenance_timeline_entries');
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('asset_downtime_records');
        Schema::dropIfExists('maintenance_incidents');
        Schema::table('maintenance_work_orders', function (Blueprint $table) {
            $table->dropForeign(['maintenance_plan_id']);
        });
        Schema::dropIfExists('maintenance_plans');
        Schema::dropIfExists('maintenance_work_orders');
        Schema::dropIfExists('maintenance_technicians');
    }
};
