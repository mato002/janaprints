<?php

namespace App\Support\Commercial\Intelligence;

use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Support\Production\JobCostingService;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommercialJobProfitabilityService
{
    public function __construct(
        protected CommercialIntelligenceQuery $query,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function snapshot(ProductionJobCard $jobCard): array
    {
        $sheet = JobCostSheet::query()
            ->where('production_job_card_id', $jobCard->id)
            ->first();

        if ($sheet === null) {
            $sheet = JobCostingService::buildOrRefresh($jobCard->loadMissing(['salesOrder', 'outsourceVendor']));
        }

        return $this->presentSheet($sheet, $jobCard);
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshotForSalesOrder(int $salesOrderId): ?array
    {
        $jobCard = ProductionJobCard::query()
            ->where('sales_order_id', $salesOrderId)
            ->latest('id')
            ->first();

        if ($jobCard === null) {
            return null;
        }

        return $this->snapshot($jobCard);
    }

    public function paginate(IntelligenceScope $scope, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query->costSheets($scope)
            ->with(['jobCard.customer', 'jobCard.salesOrder'])
            ->orderByDesc('gross_profit')
            ->paginate($perPage)
            ->through(fn (JobCostSheet $sheet) => $this->presentSheet($sheet, $sheet->jobCard));
    }

    /**
     * @return array<string, mixed>
     */
    protected function presentSheet(JobCostSheet $sheet, ?ProductionJobCard $jobCard): array
    {
        return [
            'job_card_id' => $jobCard?->id,
            'job_number' => $jobCard?->job_card_number,
            'customer_name' => $jobCard?->customer?->company_name,
            'sales_order_id' => $jobCard?->sales_order_id,
            'revenue' => round((float) $sheet->revenue, 2),
            'material_cost' => round((float) $sheet->material_cost, 2),
            'wastage_cost' => round((float) ($sheet->wastage_cost ?? 0), 2),
            'outsource_cost' => round((float) $sheet->outsourced_cost, 2),
            'total_cost' => round((float) $sheet->total_cost, 2),
            'estimated_profit' => round((float) $sheet->gross_profit, 2),
            'estimated_margin_percent' => round((float) $sheet->gross_margin_percent, 2),
        ];
    }
}
