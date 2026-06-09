<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference', 30);
            $table->date('period_start');
            $table->date('period_end');
            $table->date('pay_date');
            $table->string('status', 20)->default('draft');
            $table->unsignedSmallInteger('employee_count')->default(0);
            $table->decimal('gross_total', 14, 2)->default(0);
            $table->decimal('deductions_total', 14, 2)->default(0);
            $table->decimal('net_total', 14, 2)->default(0);
            $table->decimal('paye_total', 14, 2)->default(0);
            $table->decimal('shif_total', 14, 2)->default(0);
            $table->decimal('nssf_total', 14, 2)->default(0);
            $table->decimal('housing_levy_total', 14, 2)->default(0);
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('posted_journal_id')->nullable();
            $table->foreignId('posted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'reference']);
            $table->index(['company_id', 'status']);
            $table->index(['period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
