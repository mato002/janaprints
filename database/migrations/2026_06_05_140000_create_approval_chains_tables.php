<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_chains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('module', 50);
            $table->string('document_type', 80)->nullable();
            $table->string('approval_rule_type', 50);
            $table->string('approval_mode', 20);
            $table->string('status', 20)->default('draft');
            $table->text('description')->nullable();
            $table->json('condition_json')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(
                ['company_id', 'branch_id', 'approval_rule_type', 'status'],
                'approval_chains_scope_rule_status_idx',
            );
            $table->unique(
                ['company_id', 'branch_id', 'name'],
                'approval_chains_scope_name_unique',
            );
        });

        Schema::create('approval_chain_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_chain_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('step_number');
            $table->string('approver_role')->nullable();
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('approval_limit', 15, 2)->nullable();
            $table->boolean('is_required')->default(true);
            $table->json('condition_json')->nullable();
            $table->timestamps();

            $table->unique(
                ['approval_chain_id', 'step_number'],
                'approval_chain_steps_chain_step_unique',
            );
        });

        Schema::create('approval_chain_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approval_chain_id')->constrained()->cascadeOnDelete();
            $table->string('approval_rule_type', 50);
            $table->string('subject_type', 120);
            $table->unsignedBigInteger('subject_id');
            $table->string('status', 20)->default('pending');
            $table->json('context_json')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(
                ['company_id', 'status', 'created_at'],
                'approval_chain_runs_company_status_idx',
            );
            $table->index(
                ['subject_type', 'subject_id'],
                'approval_chain_runs_subject_idx',
            );
        });

        Schema::create('approval_chain_step_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_chain_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approval_chain_step_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('step_number');
            $table->string('status', 20)->default('pending');
            $table->foreignId('acted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(
                ['approval_chain_run_id', 'status'],
                'approval_chain_step_records_run_status_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_chain_step_records');
        Schema::dropIfExists('approval_chain_runs');
        Schema::dropIfExists('approval_chain_steps');
        Schema::dropIfExists('approval_chains');
    }
};
