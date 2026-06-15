<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('activation_role')->nullable()->after('activation_status');
        });

        Schema::table('employee_activations', function (Blueprint $table) {
            $table->string('intended_role')->nullable()->after('corporate_email');
            $table->timestamp('last_invitation_sent_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('activation_role');
        });

        Schema::table('employee_activations', function (Blueprint $table) {
            $table->dropColumn(['intended_role', 'last_invitation_sent_at']);
        });
    }
};
