<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fixed_assets') && ! Schema::hasColumn('fixed_assets', 'assigned_custodian_user_id')) {
            Schema::table('fixed_assets', function (Blueprint $table) {
                $table->foreignId('assigned_custodian_user_id')
                    ->nullable()
                    ->after('assigned_to_department_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fixed_assets') && Schema::hasColumn('fixed_assets', 'assigned_custodian_user_id')) {
            Schema::table('fixed_assets', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_custodian_user_id');
            });
        }
    }
};
