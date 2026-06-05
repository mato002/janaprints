<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module', 40);
            $table->string('tab', 40)->default('summary');
            $table->string('format', 10);
            $table->string('status', 20)->default('pending');
            $table->string('storage_path')->nullable();
            $table->string('filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['company_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
