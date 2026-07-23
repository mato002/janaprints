<?php

use App\Enums\CustomerArtworkType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_artwork_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 40);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'name']);
        });

        $now = now();

        foreach (DB::table('companies')->pluck('id') as $companyId) {
            foreach (CustomerArtworkType::cases() as $type) {
                DB::table('customer_artwork_types')->insert([
                    'company_id' => $companyId,
                    'name' => $type->label(),
                    'code' => $type->value,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_artwork_types');
    }
};
