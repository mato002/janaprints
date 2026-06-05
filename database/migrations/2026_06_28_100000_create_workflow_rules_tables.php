<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->string('description', 1000)->nullable();
            $table->string('module', 60);
            $table->string('entity_type', 80);
            $table->string('trigger', 40);
            $table->json('conditions_json')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'branch_id', 'entity_type', 'trigger', 'status'], 'workflow_rules_scope_trigger_idx');
        });

        Schema::create('workflow_rule_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_rule_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->string('action_type', 60);
            $table->json('config_json')->nullable();
            $table->timestamps();

            $table->index(['workflow_rule_id', 'sort_order'], 'workflow_rule_actions_order_idx');
        });

        Schema::create('workflow_rule_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_rule_action_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('trigger', 40);
            $table->string('status', 20);
            $table->json('result_json')->nullable();
            $table->string('error_message', 500)->nullable();
            $table->timestamp('executed_at')->useCurrent();

            $table->index(['workflow_rule_id', 'executed_at'], 'workflow_rule_executions_rule_idx');
            $table->index(['subject_type', 'subject_id'], 'workflow_rule_executions_subject_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_rule_executions');
        Schema::dropIfExists('workflow_rule_actions');
        Schema::dropIfExists('workflow_rules');
    }
};
