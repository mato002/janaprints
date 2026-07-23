<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artwork_work_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::table('artwork_requests', function (Blueprint $table) {
            $table->foreignId('artwork_work_type_id')
                ->nullable()
                ->after('quotation_id')
                ->constrained('artwork_work_types')
                ->nullOnDelete();
        });

        $requests = DB::table('artwork_requests')
            ->select('id', 'company_id', 'title')
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->get();

        $typeIdsByCompanyAndName = [];

        foreach ($requests as $request) {
            $key = $request->company_id.'|'.mb_strtolower(trim($request->title));

            if (! isset($typeIdsByCompanyAndName[$key])) {
                $existingId = DB::table('artwork_work_types')
                    ->where('company_id', $request->company_id)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->title))])
                    ->value('id');

                if ($existingId) {
                    $typeIdsByCompanyAndName[$key] = (int) $existingId;
                } else {
                    $typeIdsByCompanyAndName[$key] = (int) DB::table('artwork_work_types')->insertGetId([
                        'company_id' => $request->company_id,
                        'name' => trim($request->title),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('artwork_requests')
                ->where('id', $request->id)
                ->update(['artwork_work_type_id' => $typeIdsByCompanyAndName[$key]]);
        }
    }

    public function down(): void
    {
        Schema::table('artwork_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('artwork_work_type_id');
        });

        Schema::dropIfExists('artwork_work_types');
    }
};
