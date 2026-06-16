<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('corporate_mailboxes');

        if (Schema::hasColumn('employees', 'corporate_email')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('corporate_email');
            });
        }

        if (Schema::hasColumn('employee_activations', 'corporate_email')) {
            Schema::table('employee_activations', function (Blueprint $table) {
                $table->dropColumn('corporate_email');
            });
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('corporate_email')->nullable()->after('email');
        });

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

        Schema::table('employee_activations', function (Blueprint $table) {
            $table->string('corporate_email')->after('personal_email');
        });
    }
};
