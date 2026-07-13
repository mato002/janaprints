<?php

namespace App\Services\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\Production\ProductionJobCard;

class EstimateActualBatchComparisonService
{
    public function __construct(
        protected EstimateActualComparisonService $comparisonService,
    ) {}

    /**
     * @return array{processed: int, completed: int, failed: int}
     */
    public function compareLatestForCompany(int $companyId, int $limit = 50): array
    {
        $summary = [
            'processed' => 0,
            'completed' => 0,
            'failed' => 0,
        ];

        foreach ($this->resolveCandidates($companyId, $limit) as $candidate) {
            $summary['processed']++;

            try {
                $comparison = match ($candidate['type']) {
                    'estimate' => $this->comparisonService->compareEstimate($candidate['model']),
                    'job' => $this->comparisonService->compareJob($candidate['model']),
                    default => null,
                };

                if ($comparison === null) {
                    $summary['failed']++;

                    continue;
                }

                $summary['completed']++;
            } catch (\Throwable) {
                $summary['failed']++;
            }
        }

        return $summary;
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{type: string, id: int, model: mixed}>
     */
    public function resolveCandidates(int $companyId, int $limit): \Illuminate\Support\Collection
    {
        $estimates = PrintQuotationEstimate::query()
            ->where('company_id', $companyId)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn ($estimate) => ['type' => 'estimate', 'id' => $estimate->id, 'model' => $estimate]);

        if ($estimates->isNotEmpty()) {
            return $estimates;
        }

        return ProductionJobCard::query()
            ->where('company_id', $companyId)
            ->whereHas('materialConsumptions')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn ($job) => ['type' => 'job', 'id' => $job->id, 'model' => $job]);
    }
}
