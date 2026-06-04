<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->string('type', 30);
            $table->string('direction', 20);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('tax_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_category_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_code_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate_percent', 8, 4);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tax_code_id', 'effective_from', 'effective_to']);
        });

        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_code_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 40);
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'document_type', 'tax_code_id'], 'tax_rules_company_doc_code');
        });

        Schema::create('tax_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('tax_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_period_id')->constrained()->cascadeOnDelete();
            $table->string('return_number');
            $table->string('return_type', 30)->default('vat');
            $table->string('status', 20)->default('draft');
            $table->decimal('output_tax', 15, 2)->default(0);
            $table->decimal('input_tax', 15, 2)->default(0);
            $table->decimal('withholding_tax', 15, 2)->default(0);
            $table->decimal('net_liability', 15, 2)->default(0);
            $table->foreignId('filed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('filed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'return_number']);
        });

        Schema::create('tax_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tax_code_id')->constrained();
            $table->foreignId('tax_category_id')->constrained();
            $table->foreignId('tax_period_id')->nullable()->constrained()->nullOnDelete();
            $table->string('direction', 20);
            $table->string('source_type', 60);
            $table->unsignedBigInteger('source_id');
            $table->string('document_number')->nullable();
            $table->date('document_date');
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('rate_percent', 8, 4)->default(0);
            $table->timestamp('posted_at');
            $table->timestamps();

            $table->index(['company_id', 'document_date']);
            $table->index(['company_id', 'direction', 'tax_category_id']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('tax_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('auditable_type', 60);
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('action', 40);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_audit_logs');
        Schema::dropIfExists('tax_transactions');
        Schema::dropIfExists('tax_returns');
        Schema::dropIfExists('tax_periods');
        Schema::dropIfExists('tax_rules');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('tax_codes');
        Schema::dropIfExists('tax_categories');
    }
};
