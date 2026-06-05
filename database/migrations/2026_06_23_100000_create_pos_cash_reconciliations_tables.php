<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_cash_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pos_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->string('reconciliation_number', 40);
            $table->decimal('opening_float', 14, 2)->default(0);
            $table->decimal('cash_sales', 14, 2)->default(0);
            $table->decimal('mpesa_sales', 14, 2)->default(0);
            $table->decimal('card_sales', 14, 2)->default(0);
            $table->unsignedInteger('refunds_count')->default(0);
            $table->decimal('refund_total', 14, 2)->default(0);
            $table->decimal('expected_cash', 14, 2);
            $table->decimal('actual_cash', 14, 2);
            $table->decimal('variance', 14, 2);
            $table->string('variance_type', 20);
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'reconciliation_number']);
            $table->unique('pos_session_id');
            $table->index(['company_id', 'branch_id', 'status', 'created_at'], 'pos_recon_scope_idx');
            $table->index(['company_id', 'cashier_id', 'status'], 'pos_recon_cashier_idx');
        });

        Schema::create('pos_cash_reconciliation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_cash_reconciliation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 20);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['pos_cash_reconciliation_id', 'created_at'], 'pos_recon_log_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_cash_reconciliation_logs');
        Schema::dropIfExists('pos_cash_reconciliations');
    }
};
