<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('communication_conversations')) {
            return;
        }

        Schema::create('communication_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('conversation_code', 40);
            $table->string('conversation_type', 30);
            $table->string('status', 20)->default('open');
            $table->string('priority', 20)->default('normal');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('watcher_user_ids')->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            $table->string('last_message_preview')->nullable();
            $table->string('last_channel', 30)->nullable();
            $table->boolean('is_escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('waiting_since')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('whatsapp_conversation_id')->nullable();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->unsignedBigInteger('artwork_request_id')->nullable();
            $table->unsignedBigInteger('production_job_card_id')->nullable();
            $table->unsignedBigInteger('customer_invoice_id')->nullable();
            $table->unsignedBigInteger('customer_payment_id')->nullable();
            $table->unsignedBigInteger('supplier_bill_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'conversation_code'], 'comm_conv_code_uq');
            $table->index(['company_id', 'status', 'last_activity_at'], 'comm_conv_inbox_idx');
            $table->index(['assigned_user_id', 'status'], 'comm_conv_assign_idx');
            $table->index(['customer_id'], 'comm_conv_customer_idx');
            $table->foreign('vendor_id', 'comm_conv_vendor_fk')->references('id')->on('vendors')->nullOnDelete();
            $table->foreign('whatsapp_conversation_id', 'comm_conv_wa_fk')
                ->references('id')->on('whatsapp_conversations')->nullOnDelete();
        });

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

        Schema::create('communication_conversation_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_conversation_id');
            $table->text('body');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->json('mentioned_user_ids')->nullable();
            $table->timestamps();

            $table->foreign('communication_conversation_id', 'comm_conv_note_conv_fk')
                ->references('id')->on('communication_conversations')->cascadeOnDelete();
        });

        Schema::create('communication_conversation_attachments', function (Blueprint $table) {
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
        });

        Schema::create('communication_conversation_assignments', function (Blueprint $table) {
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
        });

        Schema::create('communication_conversation_status_history', function (Blueprint $table) {
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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_conversation_status_history');
        Schema::dropIfExists('communication_conversation_assignments');
        Schema::dropIfExists('communication_conversation_attachments');
        Schema::dropIfExists('communication_conversation_notes');
        Schema::dropIfExists('communication_conversation_messages');
        Schema::dropIfExists('communication_conversation_participants');
        Schema::dropIfExists('communication_conversations');
    }
};
