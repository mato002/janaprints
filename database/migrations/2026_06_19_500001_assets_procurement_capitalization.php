<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Superseded by 2026_06_19_100001–100003. Kept as a no-op so existing
 * migration history stays ordered on environments that already recorded it.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Schema changes live in create_asset_capitalization_tables + warranties + reconcile_assets_schema.
    }

    public function down(): void
    {
        //
    }
};
