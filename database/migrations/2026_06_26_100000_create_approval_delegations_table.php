<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delegator_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegate_user_id')->constrained('users')->cascadeOnDelete();
            $table->json('modules')->nullable();
            $table->json('approval_types')->nullable();
            $table->string('reason', 40);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status', 'start_date', 'end_date'], 'approval_delegations_company_status_dates_idx');
            $table->index(['delegator_user_id', 'status'], 'approval_delegations_delegator_status_idx');
            $table->index(['delegate_user_id', 'status'], 'approval_delegations_delegate_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_delegations');
    }
};
