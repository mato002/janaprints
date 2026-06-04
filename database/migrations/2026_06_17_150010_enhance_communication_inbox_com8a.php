<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communication_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('communication_conversations', 'tags')) {
                $table->json('tags')->nullable()->after('priority');
            }
            if (! Schema::hasColumn('communication_conversations', 'assigned_department_id')) {
                $table->unsignedBigInteger('assigned_department_id')->nullable()->after('owner_user_id');
            }
            if (! Schema::hasColumn('communication_conversations', 'assigned_team_label')) {
                $table->string('assigned_team_label', 80)->nullable()->after('assigned_department_id');
            }
            if (! Schema::hasColumn('communication_conversations', 'preferred_channel')) {
                $table->string('preferred_channel', 30)->nullable()->after('last_channel');
            }
            if (! Schema::hasColumn('communication_conversations', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable()->after('waiting_since');
            }
            if (! Schema::hasColumn('communication_conversations', 'last_staff_response_at')) {
                $table->timestamp('last_staff_response_at')->nullable()->after('first_response_at');
            }
            if (! Schema::hasColumn('communication_conversations', 'last_customer_message_at')) {
                $table->timestamp('last_customer_message_at')->nullable()->after('last_staff_response_at');
            }
            if (! Schema::hasColumn('communication_conversations', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('closed_at');
            }
            if (! Schema::hasColumn('communication_conversations', 'sla_status')) {
                $table->string('sla_status', 10)->default('green')->after('resolved_at');
            }
        });

        Schema::table('communication_conversation_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('communication_conversation_notes', 'tags')) {
                $table->json('tags')->nullable()->after('mentioned_user_ids');
            }
        });

        Schema::table('communication_conversation_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('communication_conversation_assignments', 'assigned_department_id')) {
                $table->unsignedBigInteger('assigned_department_id')->nullable()->after('to_user_id');
            }
            if (! Schema::hasColumn('communication_conversation_assignments', 'assigned_branch_id')) {
                $table->unsignedBigInteger('assigned_branch_id')->nullable()->after('assigned_department_id');
            }
        });

        if (Schema::hasColumn('communication_conversations', 'assigned_department_id')) {
            Schema::table('communication_conversations', function (Blueprint $table) {
                $table->foreign('assigned_department_id', 'comm_conv_dept_fk')
                    ->references('id')->on('departments')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('communication_conversation_assignments', 'assigned_department_id')) {
            Schema::table('communication_conversation_assignments', function (Blueprint $table) {
                $table->foreign('assigned_department_id', 'comm_conv_asgn_dept_fk')
                    ->references('id')->on('departments')->nullOnDelete();
                $table->foreign('assigned_branch_id', 'comm_conv_asgn_branch_fk')
                    ->references('id')->on('branches')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('communication_conversation_audit_events')) {
            Schema::create('communication_conversation_audit_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('communication_conversation_id');
                $table->string('event_type', 40);
                $table->json('payload')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();

                $table->index(['communication_conversation_id', 'created_at'], 'comm_conv_audit_idx');
                $table->foreign('communication_conversation_id', 'comm_conv_audit_conv_fk')
                    ->references('id')->on('communication_conversations')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_conversation_audit_events');

        Schema::table('communication_conversation_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('communication_conversation_assignments', 'assigned_branch_id')) {
                $table->dropConstrainedForeignId('assigned_branch_id');
            }
            if (Schema::hasColumn('communication_conversation_assignments', 'assigned_department_id')) {
                $table->dropConstrainedForeignId('assigned_department_id');
            }
        });

        Schema::table('communication_conversation_notes', function (Blueprint $table) {
            if (Schema::hasColumn('communication_conversation_notes', 'tags')) {
                $table->dropColumn('tags');
            }
        });

        Schema::table('communication_conversations', function (Blueprint $table) {
            foreach ([
                'sla_status', 'resolved_at', 'last_customer_message_at', 'last_staff_response_at',
                'first_response_at', 'preferred_channel', 'assigned_team_label', 'assigned_department_id', 'tags',
            ] as $col) {
                if (Schema::hasColumn('communication_conversations', $col)) {
                    if ($col === 'assigned_department_id') {
                        $table->dropForeign('comm_conv_dept_fk');
                        $table->dropColumn($col);
                    } else {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};
