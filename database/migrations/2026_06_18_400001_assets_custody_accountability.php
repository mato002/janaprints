<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->foreignId('assigned_to_employee_id')->nullable()->after('assigned_to_branch_id')->constrained('employees')->nullOnDelete();
            $table->foreignId('assigned_to_department_id')->nullable()->after('assigned_to_employee_id')->constrained('departments')->nullOnDelete();
            $table->string('current_condition', 30)->default('good')->after('assigned_to_department_id');
            $table->string('custody_status', 30)->default('unassigned')->after('current_condition');

            $table->index(['company_id', 'assigned_to_employee_id'], 'fixed_assets_company_employee_idx');
            $table->index(['company_id', 'assigned_to_department_id'], 'fixed_assets_company_department_idx');
            $table->index(['company_id', 'custody_status'], 'fixed_assets_company_custody_idx');
        });

        Schema::table('asset_assignment_histories', function (Blueprint $table) {
            $table->foreignId('assigned_to_employee_id')->nullable()->after('assigned_to_branch_id')->constrained('employees')->nullOnDelete();
            $table->foreignId('assigned_to_department_id')->nullable()->after('assigned_to_employee_id')->constrained('departments')->nullOnDelete();
            $table->date('expected_return_date')->nullable()->after('assigned_at');
            $table->string('assignment_reason', 120)->nullable()->after('expected_return_date');
            $table->string('status', 30)->default('assigned')->after('assignment_reason');
            $table->timestamp('returned_at')->nullable()->after('status');
            $table->string('condition_at_assignment', 30)->nullable()->after('returned_at');

            $table->index(['fixed_asset_id', 'status'], 'asset_assignment_histories_status_idx');
        });

        Schema::create('asset_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('handover_no', 50);
            $table->foreignId('from_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('to_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('handover_date');
            $table->date('received_date')->nullable();
            $table->text('condition_notes')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('draft');
            $table->string('condition', 30)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'handover_no'], 'asset_handovers_company_no_idx');
            $table->index(['company_id', 'status'], 'asset_handovers_company_status_idx');
            $table->index(['fixed_asset_id', 'handover_date'], 'asset_handovers_asset_idx');
        });

        Schema::create('asset_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_history_id')->nullable()->constrained('asset_assignment_histories')->nullOnDelete();
            $table->date('return_date');
            $table->string('condition', 30);
            $table->foreignId('returned_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('requires_review')->default(false);
            $table->timestamps();

            $table->index(['fixed_asset_id', 'return_date'], 'asset_returns_asset_idx');
            $table->index(['company_id', 'condition'], 'asset_returns_company_condition_idx');
        });

        Schema::create('asset_branch_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('transfer_no', 50);
            $table->foreignId('from_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->text('transfer_reason')->nullable();
            $table->string('status', 30)->default('draft');
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->string('condition', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'transfer_no'], 'asset_branch_transfers_no_idx');
            $table->index(['company_id', 'status'], 'asset_branch_transfers_status_idx');
            $table->index(['from_branch_id', 'to_branch_id'], 'asset_branch_transfers_branches_idx');
        });

        Schema::create('asset_condition_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('condition', 30);
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['fixed_asset_id', 'recorded_at'], 'asset_condition_hist_asset_idx');
        });

        Schema::create('asset_custody_timeline_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['fixed_asset_id', 'occurred_at'], 'asset_custody_timeline_asset_idx');
        });

        Schema::create('machine_operator_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(true);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['fixed_asset_id', 'is_primary'], 'machine_operator_asset_idx');
            $table->index(['employee_id', 'start_date'], 'machine_operator_employee_idx');
        });

        Schema::create('vehicle_driver_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_asset_id')->constrained('fixed_assets')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('assigned_date');
            $table->string('license_number', 50)->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_asset_id', 'assigned_date'], 'vehicle_driver_asset_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_driver_assignments');
        Schema::dropIfExists('machine_operator_assignments');
        Schema::dropIfExists('asset_custody_timeline_entries');
        Schema::dropIfExists('asset_condition_histories');
        Schema::dropIfExists('asset_branch_transfers');
        Schema::dropIfExists('asset_returns');
        Schema::dropIfExists('asset_handovers');

        Schema::table('asset_assignment_histories', function (Blueprint $table) {
            $table->dropIndex('asset_assignment_histories_status_idx');
            $table->dropConstrainedForeignId('assigned_to_employee_id');
            $table->dropConstrainedForeignId('assigned_to_department_id');
            $table->dropColumn(['expected_return_date', 'assignment_reason', 'status', 'returned_at', 'condition_at_assignment']);
        });

        Schema::table('fixed_assets', function (Blueprint $table) {
            $table->dropIndex('fixed_assets_company_employee_idx');
            $table->dropIndex('fixed_assets_company_department_idx');
            $table->dropIndex('fixed_assets_company_custody_idx');
            $table->dropConstrainedForeignId('assigned_to_employee_id');
            $table->dropConstrainedForeignId('assigned_to_department_id');
            $table->dropColumn(['current_condition', 'custody_status']);
        });
    }
};
