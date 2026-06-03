<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key');
            $table->json('value')->nullable();
            $table->string('value_type', 20)->default('string');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'key'], 'system_settings_scope_key_unique');
            $table->index(['company_id', 'key'], 'system_settings_company_key_idx');
        });

        Schema::create('form_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('form_key');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'form_key'], 'form_settings_scope_form_unique');
        });

        Schema::create('form_field_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_setting_id')->constrained()->cascadeOnDelete();
            $table->string('field_key');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->json('default_value')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['form_setting_id', 'field_key'], 'form_field_settings_form_field_unique');
        });

        Schema::create('numbering_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document_type', 50);
            $table->string('format_template')->nullable();
            $table->unsignedBigInteger('next_number')->default(1);
            $table->unsignedTinyInteger('padding')->default(5);
            $table->boolean('include_year')->default(true);
            $table->boolean('include_branch_code')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'document_type'], 'numbering_sequences_scope_doc_unique');
        });

        Schema::create('approval_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rule_type', 50);
            $table->boolean('is_enabled')->default(true);
            $table->decimal('threshold_amount', 15, 2)->nullable();
            $table->decimal('threshold_percent', 8, 2)->nullable();
            $table->string('approver_role')->nullable();
            $table->unsignedTinyInteger('min_approvers')->default(1);
            $table->json('settings_json')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'rule_type'], 'approval_rules_scope_type_unique');
            $table->index(['company_id', 'rule_type', 'is_enabled'], 'approval_rules_company_type_enabled_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_rules');
        Schema::dropIfExists('numbering_sequences');
        Schema::dropIfExists('form_field_settings');
        Schema::dropIfExists('form_settings');
        Schema::dropIfExists('system_settings');
    }
};
