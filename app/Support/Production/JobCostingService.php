<?php

namespace App\Support\Production;

use App\Enums\JobCostCategory;
use App\Models\Inventory\ProductionMaterialConsumption;
use App\Models\Production\JobCostLine;
use App\Models\Production\JobCostSheet;
use App\Models\Production\JobOverheadRate;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionWastageRecord;
use App\Enums\ProductionMaterialFlowType;
use Illuminate\Support\Facades\DB;

class JobCostingService
{
    public static function buildOrRefresh(ProductionJobCard $jobCard): JobCostSheet
    {
        return DB::transaction(function () use ($jobCard) {
            $jobCard->load(['salesOrder', 'materialConsumptions.inventoryItem', 'outsourceVendor']);

            $sheet = JobCostSheet::query()->firstOrCreate(
                ['production_job_card_id' => $jobCard->id],
                [
                    'company_id' => $jobCard->company_id,
                    'branch_id' => $jobCard->branch_id,
                    'status' => 'calculated',
                ],
            );

            $sheet->lines()
                ->whereIn('cost_category', [JobCostCategory::Material, JobCostCategory::Wastage, JobCostCategory::Outsourced])
                ->delete();

            $materialCost = 0.0;

            foreach ($jobCard->materialConsumptions as $consumption) {
                $lineTotal = (float) $consumption->quantity * (float) $consumption->unit_cost;
                $materialCost += $lineTotal;

                JobCostLine::query()->create([
                    'job_cost_sheet_id' => $sheet->id,
                    'cost_category' => JobCostCategory::Material,
                    'description' => $consumption->inventoryItem?->item_name ?? __('Material'),
                    'inventory_item_id' => $consumption->inventory_item_id,
                    'inventory_movement_id' => $consumption->inventory_movement_id,
                    'quantity' => $consumption->quantity,
                    'unit_cost' => $consumption->unit_cost,
                    'line_total' => $lineTotal,
                ]);
            }

            $wastageCost = 0.0;

            foreach (ProductionWastageRecord::query()
                ->where('production_job_card_id', $jobCard->id)
                ->where('flow_type', ProductionMaterialFlowType::Wasted)
                ->with('inventoryItem')
                ->get() as $waste) {
                $lineTotal = (float) ($waste->line_cost ?? 0);
                $wastageCost += $lineTotal;

                JobCostLine::query()->create([
                    'job_cost_sheet_id' => $sheet->id,
                    'cost_category' => JobCostCategory::Wastage,
                    'description' => $waste->waste_type?->label() ?? __('Production waste'),
                    'inventory_item_id' => $waste->inventory_item_id,
                    'quantity' => $waste->quantity,
                    'unit_cost' => $waste->quantity > 0 ? round($lineTotal / (float) $waste->quantity, 4) : 0,
                    'line_total' => $lineTotal,
                ]);
            }

            $outsourcedCost = round((float) ($jobCard->outsource_actual_cost ?? $jobCard->outsource_quoted_cost ?? 0), 2);

            if ($outsourcedCost > 0) {
                JobCostLine::query()->create([
                    'job_cost_sheet_id' => $sheet->id,
                    'cost_category' => JobCostCategory::Outsourced,
                    'description' => $jobCard->outsourceVendor?->name ?? __('Outsourced production'),
                    'line_total' => $outsourcedCost,
                ]);
            }

            $revenue = (float) ($jobCard->salesOrder?->total_amount ?? 0);
            $overhead = self::calculateOverhead($jobCard, $materialCost);
            $totalCost = round($materialCost + $wastageCost + $outsourcedCost + $overhead, 2);

            $grossProfit = round($revenue - $totalCost, 2);
            $grossMargin = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0;

            $sheet->update([
                'material_cost' => round($materialCost, 2),
                'wastage_cost' => round($wastageCost, 2),
                'outsourced_cost' => $outsourcedCost,
                'overhead_cost' => $overhead,
                'total_cost' => $totalCost,
                'revenue' => $revenue,
                'gross_profit' => $grossProfit,
                'gross_margin_percent' => $grossMargin,
                'net_profit' => $grossProfit,
                'net_margin_percent' => $grossMargin,
                'calculated_at' => now(),
                'status' => 'calculated',
            ]);

            return $sheet->fresh(['lines']);
        });
    }

    private static function calculateOverhead(ProductionJobCard $jobCard, float $materialCost): float
    {
        $rates = JobOverheadRate::query()
            ->where('company_id', $jobCard->company_id)
            ->where('is_active', true)
            ->where(function ($q) use ($jobCard) {
                $q->whereNull('branch_id')->orWhere('branch_id', $jobCard->branch_id);
            })
            ->where(function ($q) use ($jobCard) {
                $q->whereNull('production_type')
                    ->orWhere('production_type', $jobCard->production_type?->value);
            })
            ->get();

        $overhead = 0.0;

        foreach ($rates as $rate) {
            $overhead += (float) $rate->fixed_amount;
            $overhead += $materialCost * ((float) $rate->rate_percent / 100);
        }

        return round($overhead, 2);
    }

    public static function syncFromConsumption(ProductionMaterialConsumption $consumption): void
    {
        $jobCard = $consumption->jobCard ?? ProductionJobCard::query()->find($consumption->production_job_card_id);

        if ($jobCard) {
            self::buildOrRefresh($jobCard);
        }
    }
}
