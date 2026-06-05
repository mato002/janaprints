<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module', 60);
            $table->string('entity', 80)->nullable();
            $table->string('action', 80);
            $table->string('description', 500)->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('risk_level', 20)->default('low');
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->json('changed_fields')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['company_id', 'occurred_at'], 'security_audit_company_occurred_idx');
            $table->index(['company_id', 'risk_level', 'occurred_at'], 'security_audit_company_risk_idx');
            $table->index(['company_id', 'module', 'occurred_at'], 'security_audit_company_module_idx');
            $table->index(['user_id', 'occurred_at'], 'security_audit_user_occurred_idx');
            $table->index(['subject_type', 'subject_id'], 'security_audit_subject_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_audit_events');
    }
};
