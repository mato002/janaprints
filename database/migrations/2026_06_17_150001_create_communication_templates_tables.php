<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 80);
            $table->string('name');
            $table->string('category', 60);
            $table->string('channel', 20);
            $table->string('template_type', 20);
            $table->string('subject')->nullable();
            $table->text('body');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedInteger('version_number')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'channel', 'status'], 'comm_tpl_tenant_channel_status_idx');
            $table->index(['company_id', 'category'], 'comm_tpl_tenant_category_idx');
        });

        Schema::create('communication_template_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_template_id');
            $table->unsignedInteger('version_number');
            $table->unsignedBigInteger('previous_version_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->text('change_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['communication_template_id', 'version_number'], 'comm_tpl_ver_unique');

            $table->foreign('communication_template_id', 'comm_tpl_ver_template_fk')
                ->references('id')
                ->on('communication_templates')
                ->cascadeOnDelete();
            $table->foreign('previous_version_id', 'comm_tpl_ver_prev_fk')
                ->references('id')
                ->on('communication_template_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_template_versions');
        Schema::dropIfExists('communication_templates');
    }
};
