<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_credit_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->decimal('opening_credits', 12, 2)->default(0);
            $table->decimal('purchased_credits', 12, 2)->default(0);
            $table->decimal('used_credits', 12, 2)->default(0);
            $table->decimal('remaining_credits', 12, 2)->default(0);
            $table->decimal('cost_per_sms', 8, 4)->default(1);
            $table->timestamps();

            $table->unique('company_id');
        });

        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('campaign_code', 40);
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('communication_template_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message_template');
            $table->json('sample_data')->nullable();
            $table->string('send_mode', 20)->default('immediate');
            $table->string('status', 20)->default('draft');
            $table->string('recipient_source', 30);
            $table->json('recipient_filters')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('character_count')->default(0);
            $table->unsignedInteger('estimated_segments')->default(1);
            $table->decimal('cost_per_sms', 8, 4)->default(1);
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('scheduled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'campaign_code']);
            $table->index(['company_id', 'status'], 'sms_campaign_tenant_status_idx');
        });

        Schema::create('sms_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sms_campaign_id');
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('phone_number', 20);
            $table->string('display_name')->nullable();
            $table->json('variable_data')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index(['sms_campaign_id', 'status'], 'sms_recip_campaign_status_idx');
            $table->foreign('sms_campaign_id', 'sms_recip_campaign_fk')
                ->references('id')->on('sms_campaigns')->cascadeOnDelete();
        });

        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sms_campaign_id');
            $table->unsignedBigInteger('sms_recipient_id');
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone_number', 20);
            $table->text('message_body');
            $table->string('queue_status', 20)->default('queued');
            $table->string('delivery_status', 20)->nullable();
            $table->unsignedTinyInteger('segments_count')->default(1);
            $table->unsignedSmallInteger('character_count')->default(0);
            $table->decimal('credit_cost', 8, 4)->default(0);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['queue_status', 'created_at'], 'sms_msg_queue_idx');
            $table->index(['sms_campaign_id', 'delivery_status'], 'sms_msg_campaign_delivery_idx');
            $table->foreign('sms_campaign_id', 'sms_msg_campaign_fk')
                ->references('id')->on('sms_campaigns')->cascadeOnDelete();
            $table->foreign('sms_recipient_id', 'sms_msg_recipient_fk')
                ->references('id')->on('sms_recipients')->cascadeOnDelete();
        });

        Schema::create('sms_provider_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sms_message_id');
            $table->string('provider', 40)->default('jana_stub');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('sms_message_id', 'sms_prov_log_msg_fk')
                ->references('id')->on('sms_messages')->cascadeOnDelete();
        });

        Schema::create('sms_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_type', 20);
            $table->unsignedBigInteger('sms_campaign_id')->nullable();
            $table->unsignedBigInteger('sms_message_id')->nullable();
            $table->decimal('amount', 12, 4);
            $table->decimal('cost_per_sms', 8, 4)->nullable();
            $table->decimal('monetary_amount', 12, 2)->nullable();
            $table->decimal('balance_after', 12, 2);
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'created_at'], 'sms_credit_tx_company_idx');
            $table->foreign('sms_campaign_id', 'sms_credit_tx_campaign_fk')
                ->references('id')->on('sms_campaigns')->nullOnDelete();
            $table->foreign('sms_message_id', 'sms_credit_tx_msg_fk')
                ->references('id')->on('sms_messages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_credit_transactions');
        Schema::dropIfExists('sms_provider_logs');
        Schema::dropIfExists('sms_messages');
        Schema::dropIfExists('sms_recipients');
        Schema::dropIfExists('sms_campaigns');
        Schema::dropIfExists('sms_credit_balances');
    }
};
