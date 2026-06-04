<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posting_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->string('module', 30);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('posting_template_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posting_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('line_number');
            $table->string('entry_side', 10);
            $table->string('account_resolver', 30);
            $table->foreignId('gl_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('account_key', 50)->nullable();
            $table->string('context_account_field', 50)->nullable();
            $table->string('amount_source', 30);
            $table->string('amount_field', 50)->nullable();
            $table->string('line_description')->nullable();
            $table->timestamps();

            $table->unique(['posting_template_id', 'line_number']);
        });

        Schema::create('posting_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('event_code', 80);
            $table->string('module', 30);
            $table->foreignId('posting_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_post')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'event_code']);
            $table->index(['company_id', 'module', 'is_active']);
        });

        Schema::create('posting_account_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('account_key', 50);
            $table->foreignId('gl_account_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'account_key']);
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->string('posting_event', 80)->nullable()->after('entry_type');
            $table->string('source_module', 30)->nullable()->after('posting_event');
            $table->string('source_type', 100)->nullable()->after('source_module');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->foreignId('posting_template_id')->nullable()->after('source_id')->constrained()->nullOnDelete();
            $table->foreignId('posting_rule_id')->nullable()->after('posting_template_id')->constrained()->nullOnDelete();

            $table->index(['company_id', 'posting_event']);
            $table->index(['source_type', 'source_id']);
            $table->unique(['company_id', 'posting_event', 'source_type', 'source_id'], 'journals_source_posting_unique');
        });
    }

    public function down(): void
    {
        Schema::table('journals', function (Blueprint $table) {
            $table->dropUnique('journals_source_posting_unique');
            $table->dropConstrainedForeignId('posting_rule_id');
            $table->dropConstrainedForeignId('posting_template_id');
            $table->dropColumn(['posting_event', 'source_module', 'source_type', 'source_id']);
        });

        Schema::dropIfExists('posting_account_mappings');
        Schema::dropIfExists('posting_rules');
        Schema::dropIfExists('posting_template_lines');
        Schema::dropIfExists('posting_templates');
    }
};
