<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_calibration_rules')) {
            return;
        }

        Schema::create('print_calibration_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('rule_type', 40);
            $table->string('rule_key', 120);
            $table->decimal('current_value', 14, 6)->nullable();
            $table->decimal('proposed_value', 14, 6)->nullable();
            $table->decimal('variance_trigger_percent', 8, 3)->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('effective_from')->nullable();
            $table->string('rule_version', 40)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('created_by', 'pcr_created_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by', 'pcr_reviewed_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by', 'pcr_approved_by_fk')->references('id')->on('users')->nullOnDelete();

            $table->index('company_id', 'pcr_company_idx');
            $table->index('rule_type', 'pcr_type_idx');
            $table->index('status', 'pcr_status_idx');
            $table->index(['company_id', 'rule_key', 'status'], 'pcr_company_key_status_idx');
        });

        Schema::create('print_calibration_rule_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_calibration_rule_id')->constrained('print_calibration_rules')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->decimal('before_value', 14, 6)->nullable();
            $table->decimal('after_value', 14, 6)->nullable();
            $table->string('rule_version', 40)->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('effective_from')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('approved_by', 'pcrh_approved_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->index('company_id', 'pcrh_company_idx');
            $table->index('print_calibration_rule_id', 'pcrh_rule_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_calibration_rule_history');
        Schema::dropIfExists('print_calibration_rules');
    }
};
