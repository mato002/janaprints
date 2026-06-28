<?php

namespace App\Support\Commercial\Intelligence;

use App\Enums\ProductionMaterialFlowType;
use App\Models\Branch;
use App\Models\Production\JobCardSpoiledSerialRange;
use App\Models\Production\ProductionWastageRecord;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommercialWasteIntelligenceService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(IntelligenceScope $scope): array
    {
        $productionWaste = $this->productionWasteCost($scope);
        $materialWaste = $productionWaste;
        $serialSpoilage = $this->serialSpoilageMetrics($scope);
        $issuedMaterial = $this->issuedMaterialCost($scope);

        $totalWaste = $productionWaste + $serialSpoilage['estimated_cost'];
        $wastePercent = $issuedMaterial > 0 ? round(($totalWaste / $issuedMaterial) * 100, 2) : 0;

        return [
            'waste_cost' => round($totalWaste, 2),
            'production_waste_cost' => round($productionWaste, 2),
            'material_waste_cost' => round($materialWaste, 2),
            'serial_spoilage_cost' => round($serialSpoilage['estimated_cost'], 2),
            'serial_spoilage_qty' => $serialSpoilage['quantity'],
            'waste_percent' => $wastePercent,
            'top_reasons' => $this->topWasteReasons($scope),
            'by_product' => $this->wasteByProduct($scope),
            'by_branch' => $this->wasteByBranch($scope),
        ];
    }

    protected function productionWasteCost(IntelligenceScope $scope): float
    {
        if (! Schema::hasTable('production_wastage_records')) {
            return 0.0;
        }

        $query = ProductionWastageRecord::query()
            ->where('company_id', $scope->companyId)
            ->where('flow_type', ProductionMaterialFlowType::Wasted);

        if ($scope->branchId) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->fromDate) {
            $query->whereDate('recorded_at', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('recorded_at', '<=', $scope->toDate);
        }

        return (float) $query->sum('line_cost');
    }

    protected function issuedMaterialCost(IntelligenceScope $scope): float
    {
        if (! Schema::hasTable('job_cost_sheets')) {
            return 0.0;
        }

        return (float) app(CommercialIntelligenceQuery::class)
            ->costSheets($scope, false)
            ->sum(DB::raw('COALESCE(material_cost, 0) + COALESCE(wastage_cost, 0)'));
    }

    /**
     * @return array{quantity: float, estimated_cost: float}
     */
    protected function serialSpoilageMetrics(IntelligenceScope $scope): array
    {
        if (! Schema::hasTable('job_card_spoiled_serial_ranges')) {
            return ['quantity' => 0.0, 'estimated_cost' => 0.0];
        }

        $query = JobCardSpoiledSerialRange::query()
            ->whereHas('jobCard', function ($job) use ($scope) {
                $job->where('company_id', $scope->companyId);
                if ($scope->branchId) {
                    $job->where('branch_id', $scope->branchId);
                }
            })
            ->with('inventoryItem');

        if ($scope->fromDate) {
            $query->whereDate('recorded_at', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('recorded_at', '<=', $scope->toDate);
        }

        $qty = (float) $query->sum('quantity');
        $cost = (float) $query->get()->sum(function (JobCardSpoiledSerialRange $range) {
            $unit = (float) ($range->inventoryItem?->standard_cost ?? $range->inventoryItem?->average_cost ?? 0);

            return $unit * (float) $range->quantity;
        });

        return ['quantity' => $qty, 'estimated_cost' => round($cost, 2)];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function topWasteReasons(IntelligenceScope $scope, int $limit = 8): array
    {
        if (! Schema::hasTable('production_wastage_records')) {
            return [];
        }

        $query = ProductionWastageRecord::query()
            ->where('company_id', $scope->companyId)
            ->where('flow_type', ProductionMaterialFlowType::Wasted);

        if ($scope->branchId) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->fromDate) {
            $query->whereDate('recorded_at', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('recorded_at', '<=', $scope->toDate);
        }

        return $query
            ->select('waste_type', DB::raw('COALESCE(SUM(line_cost), 0) as waste_cost'), DB::raw('COALESCE(SUM(quantity), 0) as waste_qty'))
            ->groupBy('waste_type')
            ->orderByDesc('waste_cost')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'reason' => $row->waste_type ? \App\Enums\ProductionWasteType::tryFrom($row->waste_type)?->label() ?? $row->waste_type : __('Unspecified'),
                'waste_cost' => round((float) $row->waste_cost, 2),
                'waste_qty' => round((float) $row->waste_qty, 3),
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function wasteByProduct(IntelligenceScope $scope, int $limit = 10): array
    {
        if (! Schema::hasTable('production_wastage_records')) {
            return [];
        }

        $query = ProductionWastageRecord::query()
            ->where('company_id', $scope->companyId)
            ->where('flow_type', ProductionMaterialFlowType::Wasted)
            ->with('inventoryItem');

        if ($scope->branchId) {
            $query->where('branch_id', $scope->branchId);
        }

        if ($scope->fromDate) {
            $query->whereDate('recorded_at', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('recorded_at', '<=', $scope->toDate);
        }

        return $query
            ->get()
            ->groupBy(fn (ProductionWastageRecord $record) => $record->inventoryItem?->item_name ?? __('Material'))
            ->map(fn ($group, $name) => [
                'product_name' => $name,
                'waste_cost' => round((float) $group->sum('line_cost'), 2),
                'waste_qty' => round((float) $group->sum('quantity'), 3),
            ])
            ->sortByDesc('waste_cost')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function wasteByBranch(IntelligenceScope $scope): array
    {
        if (! Schema::hasTable('production_wastage_records')) {
            return [];
        }

        $query = ProductionWastageRecord::query()
            ->where('company_id', $scope->companyId)
            ->where('flow_type', ProductionMaterialFlowType::Wasted);

        if ($scope->fromDate) {
            $query->whereDate('recorded_at', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('recorded_at', '<=', $scope->toDate);
        }

        return $query
            ->select('branch_id', DB::raw('COALESCE(SUM(line_cost), 0) as waste_cost'))
            ->groupBy('branch_id')
            ->orderByDesc('waste_cost')
            ->get()
            ->map(fn ($row) => [
                'branch_name' => Branch::query()->find($row->branch_id)?->name ?? __('Unknown'),
                'waste_cost' => round((float) $row->waste_cost, 2),
            ])
            ->all();
    }
}
