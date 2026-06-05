<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_chain_step_records', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('acted_at');
            $table->timestamp('escalated_at')->nullable()->after('reminder_sent_at');
            $table->string('escalated_to_role')->nullable()->after('escalated_at');
            $table->foreignId('workflow_escalation_rule_id')
                ->nullable()
                ->after('escalated_to_role')
                ->constrained('workflow_escalation_rules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('approval_chain_step_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('workflow_escalation_rule_id');
            $table->dropColumn(['reminder_sent_at', 'escalated_at', 'escalated_to_role']);
        });
    }
};
