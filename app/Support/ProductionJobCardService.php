<?php

namespace App\Support;

use App\Enums\DocumentType;
use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Enums\ProductionType;
use App\Models\Production\ProductionJobCard;
use App\Models\Sales\SalesOrder;
use App\Support\Platform\NumberingService;
use Illuminate\Support\Facades\DB;

class ProductionJobCardService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function createFromSalesOrder(SalesOrder $salesOrder, int $createdBy, array $attributes = []): ProductionJobCard
    {
        ProductionJobCardValidator::assertCanCreateFromSalesOrder($salesOrder);

        return DB::transaction(function () use ($salesOrder, $createdBy, $attributes) {
            return ProductionJobCard::query()->create([
                'company_id' => $salesOrder->company_id,
                'branch_id' => $salesOrder->branch_id,
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'quotation_id' => $salesOrder->quotation_id,
                'artwork_request_id' => $salesOrder->artwork_request_id,
                'job_card_number' => app(NumberingService::class)->next(
                    DocumentType::JobCard,
                    $salesOrder->company_id,
                    $salesOrder->branch_id,
                ),
                'production_type' => $attributes['production_type'] ?? ProductionType::Mixed->value,
                'priority' => $attributes['priority'] ?? ProductionPriority::Normal->value,
                'planned_start_date' => $attributes['planned_start_date'] ?? null,
                'planned_end_date' => $attributes['planned_end_date'] ?? $salesOrder->required_date,
                'status' => ProductionJobCardStatus::Draft,
                'created_by' => $createdBy,
            ]);
        });
    }
}
