<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->after('company_id');
            $table->string('status', 20)->default('draft')->after('type');
            $table->decimal('budget_amount', 12, 2)->nullable()->after('duration_hours');
            $table->date('scheduled_start_date')->nullable()->after('budget_amount');
            $table->date('scheduled_end_date')->nullable()->after('scheduled_start_date');
            $table->text('evaluation_instructions')->nullable()->after('skill_tags');
            $table->timestamp('archived_at')->nullable()->after('is_active');
            $table->foreignId('duplicated_from_id')->nullable()->after('archived_at')
                ->constrained('training_programs')->nullOnDelete();
        });

        if (! Schema::hasTable('training_evaluations')) {
            Schema::create('training_evaluations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();
                $table->foreignId('employee_training_assignment_id')->nullable()
                    ->constrained()->nullOnDelete();
                $table->unsignedTinyInteger('score')->default(0);
                $table->text('feedback')->nullable();
                $table->foreignId('evaluated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('evaluated_at')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'training_program_id'], 'train_eval_program_idx');
            });
        }

        if (Schema::hasTable('training_programs') && Schema::hasColumn('training_programs', 'status')) {
            DB::table('training_programs')
                ->where('is_active', true)
                ->where('status', 'draft')
                ->update(['status' => 'active']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('training_evaluations');

        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('duplicated_from_id');
            $table->dropColumn([
                'code',
                'status',
                'budget_amount',
                'scheduled_start_date',
                'scheduled_end_date',
                'evaluation_instructions',
                'archived_at',
            ]);
        });
    }
};
