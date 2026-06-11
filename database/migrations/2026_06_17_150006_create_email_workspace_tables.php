<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_accounts')) {
            return;
        }

        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->string('reply_to_name')->nullable();
            $table->string('provider', 40)->default('unconfigured');
            $table->json('smtp_config')->nullable();
            $table->json('provider_config')->nullable();
            $table->string('status', 20)->default('inactive');
            $table->string('verification_status', 20)->default('pending');
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'email_acct_tenant_status_idx');
            $table->unique(['company_id', 'from_email'], 'email_acct_from_uq');
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('communication_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('email_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('automation_event', 40)->nullable();
            $table->string('provider_template_ref', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'communication_template_id'], 'email_tpl_com_tpl_uq');
        });

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('email_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('campaign_code', 40);
            $table->string('name');
            $table->string('campaign_type', 20)->default('single');
            $table->string('status', 20)->default('draft');
            $table->foreignId('communication_template_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('email_template_id')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->json('to_emails')->nullable();
            $table->json('cc_emails')->nullable();
            $table->json('bcc_emails')->nullable();
            $table->json('sample_data')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('opened_count')->default(0);
            $table->unsignedInteger('clicked_count')->default(0);
            $table->unsignedInteger('bounced_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'campaign_code'], 'email_camp_code_uq');
            $table->index(['company_id', 'status'], 'email_camp_status_idx');
            $table->foreign('email_template_id', 'email_camp_tpl_fk')
                ->references('id')->on('email_templates')->nullOnDelete();
        });

        Schema::create('email_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_campaign_id');
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('email');
            $table->string('display_name')->nullable();
            $table->json('variable_data')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['email_campaign_id', 'status'], 'email_recip_camp_idx');
            $table->foreign('email_campaign_id', 'email_recip_camp_fk')
                ->references('id')->on('email_campaigns')->cascadeOnDelete();
        });

        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('email_campaign_id')->nullable();
            $table->unsignedBigInteger('email_recipient_id')->nullable();
            $table->foreignId('email_account_id')->constrained()->cascadeOnDelete();
            $table->json('to_emails');
            $table->json('cc_emails')->nullable();
            $table->json('bcc_emails')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->foreignId('communication_template_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('email_template_id')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('provider_message_ref', 120)->nullable();
            $table->json('provider_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'email_msg_tenant_status_idx');
            $table->index(['email_campaign_id'], 'email_msg_camp_idx');
            $table->foreign('email_campaign_id', 'email_msg_camp_fk')
                ->references('id')->on('email_campaigns')->nullOnDelete();
            $table->foreign('email_recipient_id', 'email_msg_recip_fk')
                ->references('id')->on('email_recipients')->nullOnDelete();
            $table->foreign('email_template_id', 'email_msg_tpl_fk')
                ->references('id')->on('email_templates')->nullOnDelete();
        });

        Schema::create('email_delivery_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_message_id');
            $table->string('event', 40);
            $table->string('status_snapshot', 20)->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['email_message_id', 'created_at'], 'email_del_msg_idx');
            $table->foreign('email_message_id', 'email_del_msg_fk')
                ->references('id')->on('email_messages')->cascadeOnDelete();
        });

        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_message_id');
            $table->string('attachment_type', 30);
            $table->nullableMorphs('attachable');
            $table->string('label')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['email_message_id'], 'email_att_msg_idx');
            $table->foreign('email_message_id', 'email_att_msg_fk')
                ->references('id')->on('email_messages')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_attachments');
        Schema::dropIfExists('email_delivery_events');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('email_recipients');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_accounts');
    }
};
