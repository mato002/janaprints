<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('company_name')->nullable()->change();
        });

        $formSettingIds = DB::table('form_settings')
            ->where('form_key', 'customer')
            ->pluck('id');

        if ($formSettingIds->isNotEmpty()) {
            DB::table('form_field_settings')
                ->whereIn('form_setting_id', $formSettingIds)
                ->where('field_key', 'company_name')
                ->update(['is_required' => false]);
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('company_name')->nullable(false)->change();
        });

        $formSettingIds = DB::table('form_settings')
            ->where('form_key', 'customer')
            ->pluck('id');

        if ($formSettingIds->isNotEmpty()) {
            DB::table('form_field_settings')
                ->whereIn('form_setting_id', $formSettingIds)
                ->where('field_key', 'company_name')
                ->update(['is_required' => true]);
        }
    }
};
