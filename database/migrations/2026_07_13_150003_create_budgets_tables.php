<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('budgets')) {
            Schema::create('budgets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->foreignId('fiscal_year_id')->nullable()->constrained('fiscal_years')->nullOnDelete();
                $table->date('from_date');
                $table->date('to_date');
                $table->string('status', 32)->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['company_id', 'status']);
                $table->index(['company_id', 'from_date', 'to_date']);
            });
        }

        if (! Schema::hasTable('budget_lines')) {
            Schema::create('budget_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();
                $table->foreignId('gl_account_id')->constrained('gl_accounts')->restrictOnDelete();
                $table->string('period_month', 7)->nullable();
                $table->decimal('amount', 18, 2)->default(0);
                $table->timestamps();

                $table->index(['budget_id', 'gl_account_id']);
                $table->unique(['budget_id', 'gl_account_id', 'period_month']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
        Schema::dropIfExists('budgets');
    }
};
