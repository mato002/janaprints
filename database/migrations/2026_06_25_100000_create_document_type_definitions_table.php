<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_type_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->string('module', 30);
            $table->string('prefix', 20)->nullable();
            $table->string('number_series_key', 50)->nullable();
            $table->boolean('approval_required')->default(false);
            $table->unsignedTinyInteger('approval_levels')->default(0);
            $table->string('approval_rule_type', 50)->nullable();
            $table->unsignedInteger('retention_period_days')->nullable();
            $table->boolean('auto_numbering')->default(true);
            $table->string('status', 20)->default('active');
            $table->string('form_key', 50)->nullable();
            $table->json('workflow_json')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'code'], 'document_type_definitions_scope_code_unique');
            $table->index(['company_id', 'module', 'status'], 'document_type_definitions_company_module_status_idx');
            $table->index(['company_id', 'number_series_key'], 'document_type_definitions_company_series_idx');
            $table->index(['company_id', 'approval_rule_type'], 'document_type_definitions_company_approval_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_type_definitions');
    }
};
