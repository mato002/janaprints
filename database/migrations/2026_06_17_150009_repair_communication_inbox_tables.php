<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('communication_conversation_messages')) {
            Schema::create('communication_conversation_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('communication_conversation_id');
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('channel', 30);
                $table->string('direction', 10);
                $table->string('message_type', 20)->default('message');
                $table->text('body');
                $table->string('status', 20)->default('sent');
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->index(['source_type', 'source_id'], 'comm_conv_msg_source_idx');
                $table->unsignedBigInteger('communication_log_id')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['communication_conversation_id', 'created_at'], 'comm_conv_msg_thread_idx');
                $table->foreign('communication_conversation_id', 'comm_conv_msg_conv_fk')
                    ->references('id')->on('communication_conversations')->cascadeOnDelete();
                $table->foreign('communication_log_id', 'comm_conv_msg_log_fk')
                    ->references('id')->on('communication_logs')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('communication_conversation_participants') && Schema::hasTable('communication_conversations')) {
            Schema::create('communication_conversation_participants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('communication_conversation_id');
                $table->string('participant_type', 30);
                $table->unsignedBigInteger('participant_id')->nullable();
                $table->string('role', 20)->default('contact');
                $table->string('display_name')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->timestamps();
                $table->index(['communication_conversation_id'], 'comm_conv_part_conv_idx');
                $table->foreign('communication_conversation_id', 'comm_conv_part_conv_fk')
                    ->references('id')->on('communication_conversations')->cascadeOnDelete();
            });
        }

        foreach ([
            'communication_conversation_notes' => function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('communication_conversation_id');
                $table->text('body');
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->json('mentioned_user_ids')->nullable();
                $table->timestamps();
                $table->foreign('communication_conversation_id', 'comm_conv_note_conv_fk')
                    ->references('id')->on('communication_conversations')->cascadeOnDelete();
            },
            'communication_conversation_attachments' => function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('communication_conversation_id');
                $table->unsignedBigInteger('communication_conversation_message_id')->nullable();
                $table->string('attachment_type', 30);
                $table->string('attachable_type')->nullable();
                $table->unsignedBigInteger('attachable_id')->nullable();
                $table->index(['attachable_type', 'attachable_id'], 'comm_conv_att_morph_idx');
                $table->string('label')->nullable();
                $table->string('file_path')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->foreign('communication_conversation_id', 'comm_conv_att_conv_fk')
                    ->references('id')->on('communication_conversations')->cascadeOnDelete();
                $table->foreign('communication_conversation_message_id', 'comm_conv_att_msg_fk')
                    ->references('id')->on('communication_conversation_messages')->nullOnDelete();
            },
            'communication_conversation_assignments' => function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('communication_conversation_id');
                $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 30);
                $table->text('note')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->foreign('communication_conversation_id', 'comm_conv_asgn_conv_fk')
                    ->references('id')->on('communication_conversations')->cascadeOnDelete();
            },
            'communication_conversation_status_history' => function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('communication_conversation_id');
                $table->string('from_status', 20)->nullable();
                $table->string('to_status', 20);
                $table->string('event', 40);
                $table->json('payload')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->foreign('communication_conversation_id', 'comm_conv_stat_conv_fk')
                    ->references('id')->on('communication_conversations')->cascadeOnDelete();
            },
        ] as $name => $callback) {
            if (! Schema::hasTable($name)) {
                Schema::create($name, $callback);
            }
        }

        if (! \Illuminate\Support\Facades\DB::table('migrations')->where('migration', '2026_06_17_150008_create_communication_inbox_tables')->exists()) {
            \Illuminate\Support\Facades\DB::table('migrations')->insert([
                'migration' => '2026_06_17_150008_create_communication_inbox_tables',
                'batch' => \Illuminate\Support\Facades\DB::table('migrations')->max('batch') + 1,
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
