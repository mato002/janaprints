<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('failure_reason', 40);
            $table->timestamp('attempted_at')->useCurrent();

            $table->index(['company_id', 'attempted_at'], 'login_attempts_company_attempted_idx');
            $table->index(['email', 'attempted_at'], 'login_attempts_email_attempted_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
