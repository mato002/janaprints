<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('warehouses') || Schema::hasColumn('warehouses', 'is_virtual')) {
            return;
        }

        Schema::table('warehouses', function (Blueprint $table) {
            $table->boolean('is_virtual')->default(false)->after('is_active');
            $table->string('virtual_role', 30)->nullable()->after('is_virtual');
            $table->index(['company_id', 'is_virtual', 'virtual_role'], 'warehouses_virtual_role_idx');
            $table->unique(['company_id', 'virtual_role'], 'warehouses_company_virtual_role_uniq');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('warehouses') || ! Schema::hasColumn('warehouses', 'is_virtual')) {
            return;
        }

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropUnique('warehouses_company_virtual_role_uniq');
            $table->dropIndex('warehouses_virtual_role_idx');
            $table->dropColumn(['is_virtual', 'virtual_role']);
        });
    }
};
