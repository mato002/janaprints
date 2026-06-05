<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_backups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('relative_path')->unique();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('status')->default('available');
            $table->timestamp('backup_created_at');
            $table->timestamp('retention_until')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->json('restore_readiness')->nullable();
            $table->text('verification_message')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('retention_until');
            $table->index('backup_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_backups');
    }
};
