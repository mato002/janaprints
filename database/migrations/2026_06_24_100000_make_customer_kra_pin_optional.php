<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $formSettingIds = DB::table('form_settings')
            ->where('form_key', 'customer')
            ->pluck('id');

        if ($formSettingIds->isEmpty()) {
            return;
        }

        DB::table('form_field_settings')
            ->whereIn('form_setting_id', $formSettingIds)
            ->where('field_key', 'kra_pin')
            ->update(['is_required' => false]);
    }

    public function down(): void
    {
        $formSettingIds = DB::table('form_settings')
            ->where('form_key', 'customer')
            ->pluck('id');

        if ($formSettingIds->isEmpty()) {
            return;
        }

        DB::table('form_field_settings')
            ->whereIn('form_setting_id', $formSettingIds)
            ->where('field_key', 'kra_pin')
            ->update(['is_required' => true]);
    }
};
