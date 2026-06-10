<?php

namespace App\Services\PrintingIntelligence;

use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOutput;
use App\Models\Sales\Quotation;

class ActualProductionCostResolver
{
    public function __construct(
        protected ProductionCostRealityService $reality,
        protected RevenueResolutionService $revenueResolution,
    ) {}

    /**
     * @return array{
     *     actual_material_cost: float,
     *     actual_ink_cost: float,
     *     actual_machine_cost: float,
     *     actual_labour_cost: float,
     *     actual_overhead_cost: float,
     *     actual_total_cost: float,
     *     actual_selling_price: float|null,
     *     actual_margin_percent: float|null,
     *     job_cost_sheet_id: int|null,
     *     production_output_id: int|null,
     *     warnings: list<string>,
     *     breakdown: array<string, mixed>
     * }
     */
    public function resolve(ProductionJobCard $jobCard, ?Quotation $quotation = null): array
    {
        $warnings = [];
        $jobCard->loadMissing(['salesOrder', 'quotation', 'materialConsumptions.inventoryItem']);

        $sheet = JobCostSheet::query()
            ->where('production_job_card_id', $jobCard->id)
            ->latest('calculated_at')
            ->first();

        $output = ProductionOutput::query()
            ->where('production_job_card_id', $jobCard->id)
            ->latest('completed_at')
            ->first();

        if ($sheet === null) {
            return $this->emptyActuals($warnings, __('Job cost sheet unavailable; actual production data missing.'));
        }

        $materialCost = (float) $sheet->material_cost;
        $machineCost = (float) $sheet->machine_cost;
        $labourCost = (float) $sheet->labor_cost;
        $overheadCost = (float) $sheet->overhead_cost;
        $totalCost = (float) $sheet->total_cost;

        $inkCost = $this->resolveInkCost($jobCard, $sheet, $warnings);

        if ($inkCost <= 0 && $materialCost > 0) {
            $warnings[] = __('Ink/material split unavailable; ink cost reported as zero and material may include ink consumables.');
        }

        if ($machineCost <= 0) {
            $warnings[] = __('Actual machine cost not recorded on job cost sheet.');
        }

        if ($labourCost <= 0) {
            $warnings[] = __('Actual labour cost not recorded on job cost sheet.');
        }

        $sellingPrice = $this->revenueResolution->resolve($jobCard, $quotation, $sheet);
        if ($sellingPrice === null) {
            $warnings[] = __('Actual selling price unavailable from job cost sheet, sales order, or quotation.');
        }
        $actualMargin = null;

        if ($sellingPrice !== null && $sellingPrice > 0) {
            $actualMargin = round((($sellingPrice - $totalCost) / $sellingPrice) * 100, 3);
        } elseif ($sheet->gross_margin_percent !== null && (float) $sheet->gross_margin_percent != 0) {
            $actualMargin = (float) $sheet->gross_margin_percent;
        }

        return [
            'actual_material_cost' => $materialCost,
            'actual_ink_cost' => $inkCost,
            'actual_machine_cost' => $machineCost,
            'actual_labour_cost' => $labourCost,
            'actual_overhead_cost' => $overheadCost,
            'actual_total_cost' => $totalCost,
            'actual_selling_price' => $sellingPrice,
            'actual_margin_percent' => $actualMargin,
            'job_cost_sheet_id' => $sheet->id,
            'production_output_id' => $output?->id,
            'warnings' => $warnings,
            'breakdown' => [
                'source' => 'job_cost_sheet',
                'job_cost_sheet_id' => $sheet->id,
                'revenue' => (float) $sheet->revenue,
                'gross_profit' => (float) $sheet->gross_profit,
                'consumption_lines' => $this->reality->actualConsumption($jobCard->id),
            ],
        ];
    }

    /**
     * @param  list<string>  $warnings
     * @return array<string, mixed>
     */
    protected function emptyActuals(array &$warnings, string $message): array
    {
        $warnings[] = $message;

        return [
            'actual_material_cost' => 0.0,
            'actual_ink_cost' => 0.0,
            'actual_machine_cost' => 0.0,
            'actual_labour_cost' => 0.0,
            'actual_overhead_cost' => 0.0,
            'actual_total_cost' => 0.0,
            'actual_selling_price' => null,
            'actual_margin_percent' => null,
            'job_cost_sheet_id' => null,
            'production_output_id' => null,
            'warnings' => $warnings,
            'breakdown' => ['source' => null],
        ];
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function resolveInkCost(ProductionJobCard $jobCard, JobCostSheet $sheet, array &$warnings): float
    {
        $sheet->loadMissing('lines.inventoryItem');

        $inkTotal = 0.0;
        $inkKeywords = ['ink', 'toner', 'cartridge', 'solvent', 'cmyk'];

        foreach ($sheet->lines as $line) {
            $name = strtolower((string) ($line->inventoryItem?->item_name ?? $line->description ?? ''));
            foreach ($inkKeywords as $keyword) {
                if (str_contains($name, $keyword)) {
                    $inkTotal += (float) $line->line_total;

                    break;
                }
            }
        }

        if ($inkTotal > 0) {
            return round($inkTotal, 2);
        }

        foreach ($jobCard->materialConsumptions as $consumption) {
            $name = strtolower((string) ($consumption->inventoryItem?->item_name ?? ''));
            foreach ($inkKeywords as $keyword) {
                if (str_contains($name, $keyword)) {
                    $inkTotal += round((float) $consumption->quantity * (float) $consumption->unit_cost, 2);

                    break;
                }
            }
        }

        return round($inkTotal, 2);
    }

    /**
     * @param  list<string>  $warnings
     */
    protected function resolveSellingPrice(
        ProductionJobCard $jobCard,
        ?Quotation $quotation,
        JobCostSheet $sheet,
        array &$warnings,
    ): ?float {
        if ($quotation !== null && (float) $quotation->total_amount > 0) {
            return (float) $quotation->total_amount;
        }

        if ($jobCard->quotation !== null && (float) $jobCard->quotation->total_amount > 0) {
            return (float) $jobCard->quotation->total_amount;
        }

        if ($jobCard->salesOrder !== null && (float) $jobCard->salesOrder->total_amount > 0) {
            return (float) $jobCard->salesOrder->total_amount;
        }

        if ((float) $sheet->revenue > 0) {
            return (float) $sheet->revenue;
        }

        $warnings[] = __('Actual selling price unavailable from quotation or sales order.');

        return null;
    }
}
