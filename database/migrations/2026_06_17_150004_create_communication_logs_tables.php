<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_number', 40);
            $table->string('channel', 30);
            $table->string('communication_type', 30);
            $table->foreignId('communication_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('template_code', 80)->nullable();
            $table->string('subject')->nullable();
            $table->text('message_body');
            $table->string('status', 20)->default('draft');
            $table->string('priority', 20)->default('normal');
            $table->nullableMorphs('source');
            $table->unsignedBigInteger('sms_campaign_id')->nullable();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('read_receipt_at')->nullable();
            $table->json('provider_response')->nullable();
            $table->json('delivery_response')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'reference_number'], 'comm_log_ref_unique');
            $table->index(['company_id', 'channel', 'status'], 'comm_log_tenant_channel_idx');
            $table->index(['company_id', 'created_at'], 'comm_log_tenant_created_idx');

            $table->foreign('sms_campaign_id', 'comm_log_campaign_fk')
                ->references('id')->on('sms_campaigns')->nullOnDelete();
        });

        Schema::create('communication_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_log_id');
            $table->string('recipient_type', 30);
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('display_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('delivery_status', 20)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id'], 'comm_recip_entity_idx');
            $table->index(['communication_log_id'], 'comm_recip_log_idx');
            $table->foreign('communication_log_id', 'comm_recip_log_fk')
                ->references('id')->on('communication_logs')->cascadeOnDelete();
        });

        Schema::create('communication_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_log_id');
            $table->string('attachment_type', 30);
            $table->nullableMorphs('attachable');
            $table->string('label')->nullable();
            $table->timestamps();

            $table->foreign('communication_log_id', 'comm_attach_log_fk')
                ->references('id')->on('communication_logs')->cascadeOnDelete();
        });

        Schema::create('communication_delivery_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_log_id');
            $table->unsignedBigInteger('communication_recipient_id')->nullable();
            $table->string('event', 40);
            $table->string('status_snapshot', 20)->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['communication_log_id', 'created_at'], 'comm_delivery_log_idx');
            $table->foreign('communication_log_id', 'comm_delivery_log_fk')
                ->references('id')->on('communication_logs')->cascadeOnDelete();
            $table->foreign('communication_recipient_id', 'comm_delivery_recip_fk')
                ->references('id')->on('communication_recipients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_delivery_events');
        Schema::dropIfExists('communication_attachments');
        Schema::dropIfExists('communication_recipients');
        Schema::dropIfExists('communication_logs');
    }
};
