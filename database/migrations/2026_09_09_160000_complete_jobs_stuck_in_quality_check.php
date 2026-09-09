<?php

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\ProductionJobCard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('production_job_cards')) {
            return;
        }

        ProductionJobCard::query()
            ->where('status', ProductionJobCardStatus::QualityCheck)
            ->orderBy('id')
            ->each(function (ProductionJobCard $jobCard): void {
                $jobCard->update([
                    'status' => ProductionJobCardStatus::Completed,
                    'actual_end_date' => $jobCard->actual_end_date ?? now(),
                ]);
            });
    }

    public function down(): void
    {
        // Jobs already completed cannot be safely returned to QC.
    }
};
