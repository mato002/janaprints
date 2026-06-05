<?php

use Database\Seeders\JanaPrintsPosPostingSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new JanaPrintsPosPostingSeeder)->run();
    }

    public function down(): void
    {
        // Templates remain; no destructive rollback.
    }
};
