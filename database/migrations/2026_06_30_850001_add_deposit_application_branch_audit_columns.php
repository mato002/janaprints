<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_deposit_applications', function (Blueprint $table) {
            $table->foreignId('source_branch_id')->nullable()->after('branch_id')->constrained('branches')->nullOnDelete();
            $table->foreignId('target_branch_id')->nullable()->after('source_branch_id')->constrained('branches')->nullOnDelete();
            $table->boolean('is_cross_branch')->default(false)->after('target_branch_id');
            $table->text('override_reason')->nullable()->after('is_cross_branch');
        });

        DB::table('customer_deposit_applications')->orderBy('id')->each(function (object $application): void {
            $targetBranchId = DB::table('customer_invoices')
                ->where('id', $application->customer_invoice_id)
                ->value('branch_id');

            $sourceBranchId = $application->branch_id;
            $targetBranchId = $targetBranchId ?? $sourceBranchId;

            DB::table('customer_deposit_applications')
                ->where('id', $application->id)
                ->update([
                    'source_branch_id' => $sourceBranchId,
                    'target_branch_id' => $targetBranchId,
                    'is_cross_branch' => $sourceBranchId !== $targetBranchId,
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('customer_deposit_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('target_branch_id');
            $table->dropConstrainedForeignId('source_branch_id');
            $table->dropColumn(['is_cross_branch', 'override_reason']);
        });
    }
};
