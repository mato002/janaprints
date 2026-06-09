<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30);
            $table->string('api_url')->nullable();
            $table->text('api_key')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->string('business_account_id')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('webhook_verify_token')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('messages_sent_today')->default(0);
            $table->unsignedInteger('messages_sent_month')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('last_health_check_at')->nullable();
            $table->string('health_status', 20)->default('unknown');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'provider'], 'int_whatsapp_co_provider_uq');
            $table->index(['company_id', 'is_active'], 'int_whatsapp_co_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_whatsapp_settings');
    }
};
