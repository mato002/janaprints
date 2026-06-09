<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'branch_id']);
            $table->index(['user_id', 'is_active']);
            $table->index(['branch_id', 'is_active']);
        });

        DB::table('users')
            ->whereNotNull('default_branch_id')
            ->orderBy('id')
            ->each(function (object $user): void {
                DB::table('user_branches')->insert([
                    'user_id' => $user->id,
                    'branch_id' => $user->default_branch_id,
                    'is_primary' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_branches');
    }
};
