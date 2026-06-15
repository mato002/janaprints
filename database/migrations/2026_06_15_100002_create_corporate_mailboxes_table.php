<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_mailboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email_address')->unique();
            $table->string('local_part');
            $table->string('domain');
            $table->string('type', 30);
            $table->string('status', 30)->default('pending');
            $table->string('department_key')->nullable();
            $table->string('system_key')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->text('provision_error')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'type']);
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_mailboxes');
    }
};
