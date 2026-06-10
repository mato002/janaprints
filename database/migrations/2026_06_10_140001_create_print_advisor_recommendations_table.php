<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('print_advisor_recommendations')) {
            return;
        }

        Schema::create('print_advisor_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('recommendation_type', 30);
            $table->string('severity', 20);
            $table->string('status', 20)->default('open');
            $table->string('title', 255);
            $table->text('summary');
            $table->text('recommendation_text');
            $table->string('source_module', 20);
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->json('evidence')->nullable();
            $table->text('recommended_action')->nullable();
            $table->string('entity_type', 100)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('rule_code', 120);
            $table->text('comment')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->unsignedBigInteger('dismissed_by')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->foreign('branch_id', 'par_branch_fk')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('acknowledged_by', 'par_ack_by_fk')->references('id')->on('users')->nullOnDelete();
            $table->foreign('dismissed_by', 'par_dismiss_by_fk')->references('id')->on('users')->nullOnDelete();

            $table->index('company_id', 'par_company_idx');
            $table->index('recommendation_type', 'par_type_idx');
            $table->index('severity', 'par_severity_idx');
            $table->index('status', 'par_status_idx');
            $table->unique(['company_id', 'rule_code'], 'par_company_rule_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_advisor_recommendations');
    }
};
