<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_email_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30);
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('smtp_host')->nullable();
            $table->unsignedSmallInteger('smtp_port')->nullable();
            $table->string('smtp_encryption', 10)->nullable();
            $table->string('smtp_username')->nullable();
            $table->text('smtp_password')->nullable();
            $table->string('mailgun_domain')->nullable();
            $table->text('mailgun_api_key')->nullable();
            $table->text('sendgrid_api_key')->nullable();
            $table->string('ses_access_key')->nullable();
            $table->text('ses_secret_key')->nullable();
            $table->string('ses_region', 30)->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('last_test_success')->nullable();
            $table->timestamp('last_successful_send_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->text('last_failure_message')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'provider'], 'int_email_co_provider_uq');
            $table->index(['company_id', 'is_active'], 'int_email_co_active_idx');
        });

        Schema::create('integration_sms_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30);
            $table->string('api_url')->nullable();
            $table->text('api_key')->nullable();
            $table->string('sender_id', 20)->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('callback_url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sms_sent_today')->default(0);
            $table->unsignedInteger('sms_sent_month')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('last_health_check_at')->nullable();
            $table->string('health_status', 20)->default('unknown');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'provider'], 'int_sms_co_provider_uq');
            $table->index(['company_id', 'is_active'], 'int_sms_co_active_idx');
        });

        Schema::create('integration_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('key', 64)->unique();
            $table->string('secret_prefix', 12);
            $table->string('secret_hash');
            $table->string('environment', 20)->default('production');
            $table->json('allowed_ips')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'is_active'], 'int_api_keys_co_active_idx');
            $table->index(['company_id', 'environment'], 'int_api_keys_co_env_idx');
        });

        Schema::create('integration_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('endpoint_url');
            $table->text('secret')->nullable();
            $table->json('event_types');
            $table->string('status', 20)->default('active');
            $table->unsignedTinyInteger('retry_count')->default(3);
            $table->timestamp('last_delivery_at')->nullable();
            $table->unsignedSmallInteger('last_response_code')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'int_webhooks_co_status_idx');
        });

        Schema::create('integration_webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained('integration_webhooks')->cascadeOnDelete();
            $table->string('event_type', 60);
            $table->json('payload');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedTinyInteger('attempt_count')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['webhook_id', 'status'], 'int_wh_deliveries_wh_status_idx');
            $table->index(['webhook_id', 'created_at'], 'int_wh_deliveries_wh_created_idx');
        });

        Schema::create('integration_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30);
            $table->string('provider_key', 60);
            $table->string('name');
            $table->string('status', 20)->default('disconnected');
            $table->text('config')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'provider_key'], 'int_providers_co_key_uq');
            $table->index(['company_id', 'status'], 'int_providers_co_status_idx');
        });

        Schema::create('integration_provider_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('integration_providers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);
            $table->string('status', 20)->default('success');
            $table->text('message')->nullable();
            $table->json('properties')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['provider_id', 'created_at'], 'int_provider_logs_prov_created_idx');
        });

        Schema::create('integration_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('integration_providers')->cascadeOnDelete();
            $table->string('sync_type', 40);
            $table->string('status', 20)->default('success');
            $table->unsignedInteger('records_synced')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'created_at'], 'int_sync_logs_prov_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_sync_logs');
        Schema::dropIfExists('integration_provider_logs');
        Schema::dropIfExists('integration_providers');
        Schema::dropIfExists('integration_webhook_deliveries');
        Schema::dropIfExists('integration_webhooks');
        Schema::dropIfExists('integration_api_keys');
        Schema::dropIfExists('integration_sms_settings');
        Schema::dropIfExists('integration_email_settings');
    }
};
