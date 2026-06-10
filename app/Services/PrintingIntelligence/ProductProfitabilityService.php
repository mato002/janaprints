<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;

class ProductProfitabilityService
{
    /**
     * @param  array{company_id?: int, branch_id?: int|null, days?: int}  $filters
     * @return array<string, mixed>
     */
    public function analyze(array $filters = []): array
    {
        $companyId = (int) ($filters['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $days = (int) ($filters['days'] ?? 90);

        $productQuery = PrintProfitabilitySnapshot::query()
            ->where('company_id', $companyId)
            ->where('snapshot_type', ProfitabilitySnapshotType::Product)
            ->where('snapshot_date', '>=', now()->subDays($days)->toDateString());

        PrintingIntelligenceScope::applyBranchScope($productQuery, $filters);

        $productRows = PrintingIntelligenceScope::dedupeSnapshotsByLatestDate(
            $productQuery->get(),
            fn ($row) => $row->metadata['product_key'] ?? 'unknown',
        );

        if ($productRows->isNotEmpty()) {
            $ranked = $productRows->sortByDesc('gross_profit')->values();
        } else {
            $jobQuery = PrintProfitabilitySnapshot::query()
                ->where('company_id', $companyId)
                ->where('snapshot_type', ProfitabilitySnapshotType::Job)
                ->where('snapshot_date', '>=', now()->subDays($days)->toDateString());

            PrintingIntelligenceScope::applyBranchScope($jobQuery, $filters);

            $ranked = $jobQuery->get()
                ->groupBy(fn ($r) => $r->metadata['production_type'] ?? 'unknown')
                ->map(function ($group, $key) {
                    $revenue = $group->sum(fn ($r) => (float) $r->revenue);
                    $profit = $group->sum(fn ($r) => (float) $r->gross_profit);

                    return (object) [
                        'metadata' => [
                            'product_key' => $key,
                            'product_label' => config("printing_intelligence.product_type_labels.{$key}", ucfirst(str_replace('_', ' ', $key))),
                            'job_count' => $group->count(),
                        ],
                        'revenue' => $revenue,
                        'total_cost' => $group->sum(fn ($r) => (float) $r->total_cost),
                        'gross_profit' => $profit,
                        'gross_margin_percent' => $revenue > 0 ? ($profit / $revenue) * 100 : null,
                    ];
                })->sortByDesc(fn ($r) => (float) $r->gross_profit)->values();
        }

        return [
            'highest_margin' => $this->formatProduct($ranked->first()),
            'lowest_margin' => $this->formatProduct($ranked->last()),
            'rankings' => $ranked->take(10)->map(fn ($r) => $this->formatProduct($r))->filter()->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function formatProduct(mixed $row): ?array
    {
        if ($row === null) {
            return null;
        }

        $revenue = (float) ($row->revenue ?? 0);
        $profit = (float) ($row->gross_profit ?? 0);

        return [
            'product_key' => $row->metadata['product_key'] ?? null,
            'product_label' => $row->metadata['product_label'] ?? __('Unknown'),
            'revenue' => round($revenue, 2),
            'cost' => round((float) ($row->total_cost ?? 0), 2),
            'profit' => round($profit, 2),
            'margin_percent' => $row->gross_margin_percent !== null ? round((float) $row->gross_margin_percent, 3) : ($revenue > 0 ? round(($profit / $revenue) * 100, 3) : null),
            'job_count' => (int) ($row->metadata['job_count'] ?? 0),
        ];
    }
}
