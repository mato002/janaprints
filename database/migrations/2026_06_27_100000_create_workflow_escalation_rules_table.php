<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('workflow_key', 80);
            $table->unsignedSmallInteger('waiting_hours');
            $table->string('escalation_target_role');
            $table->string('escalation_method', 20);
            $table->string('status', 20)->default('draft');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(
                ['company_id', 'branch_id', 'workflow_key', 'status'],
                'workflow_escalation_rules_scope_workflow_status_idx',
            );
            $table->unique(
                ['company_id', 'branch_id', 'name'],
                'workflow_escalation_rules_scope_name_unique',
            );
        });

        Schema::create('workflow_escalation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_escalation_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approval_chain_step_record_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 30);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['approval_chain_step_record_id', 'event_type'],
                'workflow_escalation_events_record_type_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_escalation_events');
        Schema::dropIfExists('workflow_escalation_rules');
    }
};
