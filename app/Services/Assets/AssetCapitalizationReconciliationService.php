<?php

namespace App\Services\Assets;

use App\Enums\AssetAcquisitionSource;
use App\Enums\AssetCapitalizationReconciliationStatus;
use App\Enums\CapitalizationCandidateStatus;
use App\Enums\DocumentType;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\Assets\AssetCapitalizationReconciliation;
use App\Models\Assets\FixedAsset;
use App\Support\Platform\NumberingService;

class AssetCapitalizationReconciliationService
{
    public function run(int $companyId, int $userId): AssetCapitalizationReconciliation
    {
        $receivedValue = (float) AssetCapitalizationCandidate::query()
            ->where('company_id', $companyId)
            ->whereIn('status', [
                CapitalizationCandidateStatus::Ready->value,
                CapitalizationCandidateStatus::Pending->value,
                CapitalizationCandidateStatus::Capitalized->value,
            ])
            ->sum('line_amount');

        $capitalizedValue = (float) FixedAsset::query()
            ->where('company_id', $companyId)
            ->where('acquisition_source', AssetAcquisitionSource::Procurement)
            ->sum('acquisition_cost');

        $postedValue = (float) FixedAsset::query()
            ->where('company_id', $companyId)
            ->where('acquisition_source', AssetAcquisitionSource::Procurement)
            ->whereNotNull('posted_acquisition_journal_id')
            ->sum('acquisition_cost');

        $registerValue = $capitalizedValue;

        $receivedNotCapitalized = AssetCapitalizationCandidate::query()
            ->where('company_id', $companyId)
            ->whereIn('status', [CapitalizationCandidateStatus::Ready->value, CapitalizationCandidateStatus::Pending->value])
            ->count();

        $capitalizedNotPosted = FixedAsset::query()
            ->where('company_id', $companyId)
            ->where('acquisition_source', AssetAcquisitionSource::Procurement)
            ->whereNull('posted_acquisition_journal_id')
            ->count();

        $postedNotRegistered = 0;

        $variances = [];
        $valueDiff = round($receivedValue - $capitalizedValue, 2);

        if ($receivedNotCapitalized > 0) {
            $variances[] = ['type' => 'received_not_capitalized', 'count' => $receivedNotCapitalized];
        }

        if ($capitalizedNotPosted > 0) {
            $variances[] = ['type' => 'capitalized_not_posted', 'count' => $capitalizedNotPosted];
        }

        if (abs($valueDiff) > 0.01) {
            $variances[] = ['type' => 'value_difference', 'amount' => $valueDiff];
        }

        $status = match (true) {
            $receivedNotCapitalized > 0 || $capitalizedNotPosted > 0 => AssetCapitalizationReconciliationStatus::Critical,
            abs($valueDiff) > 0.01 => AssetCapitalizationReconciliationStatus::Variance,
            default => AssetCapitalizationReconciliationStatus::Balanced,
        };

        return AssetCapitalizationReconciliation::query()->create([
            'company_id' => $companyId,
            'reconciliation_number' => app(NumberingService::class)->next(
                DocumentType::AssetCapitalizationReconciliation,
                $companyId,
            ),
            'reconciliation_date' => now()->toDateString(),
            'procurement_received_value' => $receivedValue,
            'capitalized_value' => $capitalizedValue,
            'posted_value' => $postedValue,
            'register_value' => $registerValue,
            'received_not_capitalized_count' => $receivedNotCapitalized,
            'capitalized_not_posted_count' => $capitalizedNotPosted,
            'posted_not_registered_count' => $postedNotRegistered,
            'status' => $status,
            'variance_details' => $variances,
            'run_by' => $userId,
        ]);
    }
}
