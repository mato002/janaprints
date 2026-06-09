<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('leave_types', 'unit')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->string('unit', 10)->default('days')->after('name');
            });
        }

        if (! Schema::hasColumn('public_holidays', 'region')) {
            Schema::table('public_holidays', function (Blueprint $table) {
                $table->string('region', 100)->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('public_holidays', 'is_recurring')) {
            Schema::table('public_holidays', function (Blueprint $table) {
                $table->boolean('is_recurring')->default(false)->after('holiday_date');
            });
        }

        if (! Schema::hasTable('leave_policies')) {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('min_notice_days')->default(0);
            $table->decimal('max_consecutive_days', 5, 1)->nullable();
            $table->boolean('requires_documentation')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'leave_type_id']);
        });
        }

        if (! Schema::hasTable('leave_accrual_rules')) {
        Schema::create('leave_accrual_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->string('frequency', 20)->default('monthly');
            $table->decimal('rate_per_period', 6, 2)->default(0);
            $table->unsignedSmallInteger('custom_interval_days')->nullable();
            $table->date('effective_from')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'leave_type_id', 'is_active'], 'leave_accrual_co_type_active_idx');
        });
        }

        if (! Schema::hasTable('leave_carry_forward_rules')) {
        Schema::create('leave_carry_forward_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_policy_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('max_carry_days', 5, 1)->default(0);
            $table->unsignedTinyInteger('expiry_month')->nullable();
            $table->unsignedTinyInteger('expiry_day')->nullable();
            $table->text('policy_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'leave_type_id', 'is_active'], 'leave_carry_co_type_active_idx');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_carry_forward_rules');
        Schema::dropIfExists('leave_accrual_rules');
        Schema::dropIfExists('leave_policies');

        Schema::table('public_holidays', function (Blueprint $table) {
            $table->dropColumn(['region', 'is_recurring']);
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
