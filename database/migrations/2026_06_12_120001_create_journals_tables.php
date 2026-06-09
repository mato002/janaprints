<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('accounting_period_id')->constrained()->cascadeOnDelete();
            $table->string('journal_number');
            $table->date('journal_date');
            $table->string('entry_type', 20)->default('manual');
            $table->string('status', 20)->default('draft');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->foreignId('reversal_of_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->foreignId('reversed_by_journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'journal_number']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'journal_date']);
            $table->index(['accounting_period_id', 'status']);
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gl_account_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('line_number');
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['journal_id', 'line_number']);
            $table->index(['gl_account_id']);
        });

        if (Schema::hasTable('payroll_runs') && Schema::hasColumn('payroll_runs', 'posted_journal_id')) {
            Schema::table('payroll_runs', function (Blueprint $table) {
                $table->foreign('posted_journal_id', 'payroll_runs_posted_journal_fk')
                    ->references('id')
                    ->on('journals')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payroll_runs')) {
            Schema::table('payroll_runs', function (Blueprint $table) {
                $table->dropForeign('payroll_runs_posted_journal_fk');
            });
        }

        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journals');
    }
};
