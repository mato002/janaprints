<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone_number', 20);
            $table->string('display_name')->nullable();
            $table->string('provider', 40)->default('unconfigured');
            $table->string('provider_account_ref', 120)->nullable();
            $table->string('status', 20)->default('inactive');
            $table->string('verification_status', 20)->default('pending');
            $table->boolean('is_default')->default(false);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'wa_acct_tenant_status_idx');
            $table->unique(['company_id', 'phone_number'], 'wa_acct_phone_uq');
        });

        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('communication_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('automation_event', 40)->nullable();
            $table->string('provider_template_ref', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'communication_template_id'], 'wa_tpl_com_tpl_uq');
            $table->index(['company_id', 'automation_event'], 'wa_tpl_auto_evt_idx');
        });

        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
            $table->string('conversation_code', 40);
            $table->string('phone_number', 20);
            $table->string('channel', 20)->default('whatsapp');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('tags')->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            $table->string('last_message_preview')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'conversation_code'], 'wa_conv_code_uq');
            $table->index(['company_id', 'status', 'last_activity_at'], 'wa_conv_inbox_idx');
            $table->index(['customer_id'], 'wa_conv_customer_idx');
            $table->foreign('vendor_id', 'wa_conv_vendor_fk')
                ->references('id')->on('vendors')->nullOnDelete();
        });

        Schema::create('whatsapp_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_conversation_id');
            $table->string('participant_type', 30);
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('display_name')->nullable();
            $table->string('role', 20)->default('contact');
            $table->timestamps();

            $table->index(['whatsapp_conversation_id'], 'wa_part_conv_idx');
            $table->foreign('whatsapp_conversation_id', 'wa_part_conv_fk')
                ->references('id')->on('whatsapp_conversations')->cascadeOnDelete();
        });

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('whatsapp_conversation_id');
            $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 10);
            $table->string('message_type', 20);
            $table->text('body');
            $table->foreignId('communication_template_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('whatsapp_template_id')->nullable();
            $table->string('status', 20)->default('queued');
            $table->string('provider_message_ref', 120)->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'wa_msg_tenant_status_idx');
            $table->index(['whatsapp_conversation_id', 'created_at'], 'wa_msg_conv_idx');
            $table->foreign('whatsapp_conversation_id', 'wa_msg_conv_fk')
                ->references('id')->on('whatsapp_conversations')->cascadeOnDelete();
            $table->foreign('whatsapp_template_id', 'wa_msg_tpl_fk')
                ->references('id')->on('whatsapp_templates')->nullOnDelete();
        });

        Schema::create('whatsapp_delivery_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_message_id');
            $table->string('event', 40);
            $table->string('status_snapshot', 20)->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['whatsapp_message_id', 'created_at'], 'wa_del_msg_idx');
            $table->foreign('whatsapp_message_id', 'wa_del_msg_fk')
                ->references('id')->on('whatsapp_messages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_delivery_events');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_participants');
        Schema::dropIfExists('whatsapp_conversations');
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_accounts');
    }
};
