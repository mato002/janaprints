<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('gl_account_id')->constrained('gl_accounts')->restrictOnDelete();
                $table->string('name');
                $table->string('account_number')->nullable();
                $table->string('currency_code', 3)->default('KES');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'gl_account_id']);
                $table->index(['company_id', 'is_active']);
            });
        }

        if (! Schema::hasTable('bank_statements')) {
            Schema::create('bank_statements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
                $table->date('statement_date');
                $table->decimal('opening_balance', 18, 2)->default(0);
                $table->decimal('closing_balance', 18, 2)->default(0);
                $table->string('status', 32)->default('draft');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reconciled_at')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'bank_account_id', 'statement_date']);
                $table->index(['company_id', 'status']);
            });
        }

        if (! Schema::hasTable('bank_statement_lines')) {
            Schema::create('bank_statement_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bank_statement_id')->constrained('bank_statements')->cascadeOnDelete();
                $table->date('line_date');
                $table->string('description');
                $table->string('reference')->nullable();
                $table->decimal('amount', 18, 2);
                $table->foreignId('matched_journal_line_id')->nullable()->constrained('journal_lines')->nullOnDelete();
                $table->boolean('is_matched')->default(false);
                $table->timestamps();

                $table->index(['bank_statement_id', 'is_matched']);
                $table->index(['bank_statement_id', 'line_date']);
            });
        }

        if (! Schema::hasTable('bank_reconciliations')) {
            Schema::create('bank_reconciliations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bank_statement_id')->constrained('bank_statements')->cascadeOnDelete();
                $table->decimal('statement_closing_balance', 18, 2);
                $table->decimal('gl_closing_balance', 18, 2);
                $table->decimal('difference', 18, 2)->default(0);
                $table->timestamp('reconciled_at');
                $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique('bank_statement_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('bank_accounts');
    }
};
