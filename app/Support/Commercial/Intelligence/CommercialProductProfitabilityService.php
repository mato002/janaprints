<?php

namespace App\Support\Commercial\Intelligence;

use App\Models\Production\JobCostSheet;
use App\Support\Production\ProductProfitabilityService;
use App\Support\Reports\IntelligenceScope;

class CommercialProductProfitabilityService
{
    public function __construct(
        protected CommercialIntelligenceQuery $query,
        protected ProductProfitabilityService $productionProfitability,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function byProductionType(IntelligenceScope $scope, int $limit = 20): array
    {
        return $this->productionProfitability->ranking(
            $scope->companyId,
            $scope->branchId,
            [
                'date_from' => $scope->fromDate,
                'date_to' => $scope->toDate,
            ],
            $limit,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function byProductName(IntelligenceScope $scope, int $limit = 25): array
    {
        $sheets = $this->query->costSheets($scope)
            ->with(['jobCard.salesOrder.items'])
            ->get();

        $grouped = [];

        foreach ($sheets as $sheet) {
            $items = $sheet->jobCard?->salesOrder?->items ?? collect();
            $orderRevenue = (float) $sheet->revenue;
            $lineTotalSum = max(0.01, (float) $items->sum('line_total'));

            if ($items->isEmpty()) {
                $key = $sheet->jobCard?->production_type
                    ? str($sheet->jobCard->production_type->value)->headline()
                    : __('Unspecified');
                $this->accumulateProductRow($grouped, $key, $orderRevenue, $sheet);

                continue;
            }

            foreach ($items as $item) {
                $share = $orderRevenue * ((float) $item->line_total / $lineTotalSum);
                $costShare = (float) $sheet->total_cost * ((float) $item->line_total / $lineTotalSum);
                $wasteShare = (float) ($sheet->wastage_cost ?? 0) * ((float) $item->line_total / $lineTotalSum);
                $this->accumulateProductRow($grouped, (string) $item->item_name, $share, $sheet, $costShare, $wasteShare);
            }
        }

        return collect($grouped)
            ->map(function (array $row) {
                $profit = $row['revenue'] - $row['total_cost'];

                return [
                    'product_name' => $row['product_name'],
                    'orders' => $row['orders'],
                    'revenue' => round($row['revenue'], 2),
                    'material_cost' => round($row['material_cost'], 2),
                    'waste_cost' => round($row['waste_cost'], 2),
                    'total_cost' => round($row['total_cost'], 2),
                    'profit' => round($profit, 2),
                    'margin_percent' => $row['revenue'] > 0 ? round(($profit / $row['revenue']) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('profit')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array{most_profitable: list<array<string, mixed>>, least_profitable: list<array<string, mixed>>}
     */
    public function rankings(IntelligenceScope $scope, int $limit = 5): array
    {
        $rows = $this->byProductName($scope, 50);

        return [
            'most_profitable' => array_slice($rows, 0, $limit),
            'least_profitable' => array_slice(array_reverse($rows), 0, $limit),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $grouped
     */
    protected function accumulateProductRow(
        array &$grouped,
        string $productName,
        float $revenueShare,
        JobCostSheet $sheet,
        ?float $costShare = null,
        ?float $wasteShare = null,
    ): void {
        $key = $productName !== '' ? $productName : __('Unspecified');

        if (! isset($grouped[$key])) {
            $grouped[$key] = [
                'product_name' => $key,
                'orders' => 0,
                'revenue' => 0.0,
                'material_cost' => 0.0,
                'waste_cost' => 0.0,
                'total_cost' => 0.0,
            ];
        }

        $grouped[$key]['orders']++;
        $grouped[$key]['revenue'] += $revenueShare;
        $grouped[$key]['material_cost'] += $costShare ?? (float) $sheet->material_cost;
        $grouped[$key]['waste_cost'] += $wasteShare ?? (float) ($sheet->wastage_cost ?? 0);
        $grouped[$key]['total_cost'] += $costShare ?? (float) $sheet->total_cost;
    }
}
