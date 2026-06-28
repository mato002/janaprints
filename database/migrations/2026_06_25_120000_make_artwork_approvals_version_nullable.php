<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artwork_approvals', function (Blueprint $table) {
            $table->dropForeign(['artwork_version_id']);
            $table->foreignId('artwork_version_id')->nullable()->change();
            $table->foreign('artwork_version_id')
                ->references('id')
                ->on('artwork_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('artwork_approvals', function (Blueprint $table) {
            $table->dropForeign(['artwork_version_id']);
            $table->foreignId('artwork_version_id')->nullable(false)->change();
            $table->foreign('artwork_version_id')
                ->references('id')
                ->on('artwork_versions')
                ->cascadeOnDelete();
        });
    }
};
