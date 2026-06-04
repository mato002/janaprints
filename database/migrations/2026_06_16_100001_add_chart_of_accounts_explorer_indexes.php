<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->index(
                ['company_id', 'gl_account_type_id', 'gl_account_group_id'],
                'gl_accounts_company_type_group_idx',
            );
        });

        Schema::table('gl_account_groups', function (Blueprint $table) {
            $table->index(
                ['company_id', 'gl_account_type_id', 'parent_id'],
                'gl_account_groups_company_type_parent_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->dropIndex('gl_accounts_company_type_group_idx');
        });

        Schema::table('gl_account_groups', function (Blueprint $table) {
            $table->dropIndex('gl_account_groups_company_type_parent_idx');
        });
    }
};
