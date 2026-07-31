<?php

namespace App\Support\Production;

use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Models\Production\ProductionSpecification;

class JobCardJobSheetPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(ProductionJobCard $jobCard): array
    {
        $jobCard->loadMissing([
            'customer:id,company_name',
            'company:id,name,phone,email,address',
            'branch:id,name',
            'salesOrder.items',
            'productionSpecification.paperInventoryItem:id,item_name',
            'productionSpecification.materialInventoryItem:id,item_name',
            'serialAllocation',
            'materialRequirements.inventoryItem:id,item_name',
            'creator:id,name',
        ]);

        $spec = $jobCard->productionSpecification;
        $lineItem = $jobCard->salesOrder?->items->first();
        $quantity = $spec?->quantity ?? $lineItem?->quantity ?? $jobCard->planned_quantity;
        $description = $spec?->product_description
            ?? $lineItem?->item_name
            ?? $jobCard->inventoryItem?->item_name
            ?? '—';

        return [
            'job_number' => $jobCard->job_card_number,
            'date' => $jobCard->created_at?->format('d/m/Y') ?? now()->format('d/m/Y'),
            'customer_name' => $jobCard->customer?->company_name ?? '—',
            'company_name' => $jobCard->company?->name ?? config('app.name'),
            'company_phone' => $jobCard->company?->phone ?? '',
            'company_email' => $jobCard->company?->email ?? '',
            'company_address' => $jobCard->company?->address ?? '',
            'printing_rows' => $this->printingRows($spec, $description, $quantity),
            'binding' => [
                'serial_start' => $this->serialStart($jobCard, $spec),
                'pages_per_pad' => $this->pagesPerPadLabel($spec),
                'size' => $spec?->finished_size ?? $spec?->size ?? '—',
                'ups' => $spec?->ups ?? '—',
                'binding' => $spec?->binding_type ?? '—',
                'collection_date' => $jobCard->required_date?->format('d/m/Y') ?? '—',
            ],
            'notes' => trim(collect([
                $spec?->production_notes,
                $spec?->delivery_notes,
            ])->filter()->implode("\n")) ?: '—',
            'material_rows' => $this->materialRows($jobCard),
            'prepared_by' => $jobCard->creator?->name ?? auth()->user()?->name ?? '—',
            'status' => [
                'printed' => $jobCard->status->value !== 'queued',
                'complete' => in_array($jobCard->status->value, ['completed', 'ready_for_dispatch'], true),
                'collected' => $jobCard->status->value === 'completed',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function printingRows(?ProductionSpecification $spec, string $description, mixed $quantity): array
    {
        $paperStock = $spec?->paperInventoryItem?->item_name
            ?? $spec?->materialInventoryItem?->item_name
            ?? '—';
        $ink = collect([$spec?->colour_mode, $spec?->ink_type?->label()])
            ->filter()
            ->implode(' / ') ?: '—';

        $ncrColours = $this->ncrColours($spec);

        return [[
            'quantity' => $quantity ?? '—',
            'description' => $description,
            'orig' => $ncrColours['orig'],
            'dup' => $ncrColours['dup'],
            'tri' => $ncrColours['tri'],
            'quad' => $ncrColours['quad'],
            'paper_stock' => $paperStock,
            'ink' => $ink,
        ]];
    }

    /**
     * @return array{orig: string, dup: string, tri: string, quad: string}
     */
    protected function ncrColours(?ProductionSpecification $spec): array
    {
        $payload = is_array($spec?->snapshot_payload) ? $spec->snapshot_payload : [];
        $colours = is_array($payload['ncr_colours'] ?? null) ? $payload['ncr_colours'] : [];

        return [
            'orig' => (string) ($colours['orig'] ?? $colours['original'] ?? '—'),
            'dup' => (string) ($colours['dup'] ?? $colours['duplicate'] ?? '—'),
            'tri' => (string) ($colours['tri'] ?? $colours['triplicate'] ?? '—'),
            'quad' => (string) ($colours['quad'] ?? $colours['quadruplicate'] ?? '—'),
        ];
    }

    protected function serialStart(ProductionJobCard $jobCard, ?ProductionSpecification $spec): string
    {
        $allocation = $jobCard->serialAllocation;
        if ($allocation) {
            return $allocation->formatSerial($allocation->serial_start);
        }

        if ($spec?->numbering_required) {
            return __('Required');
        }

        return '—';
    }

    /**
     * @return list<array{paper_type: string, sheets_a4_a3: string, sheets_a1: string}>
     */
    protected function materialRows(ProductionJobCard $jobCard): array
    {
        $requirements = $jobCard->materialRequirements
            ->map(function (ProductionMaterialRequirement $requirement) {
                return [
                    'paper_type' => $requirement->inventoryItem?->item_name ?? __('Material'),
                    'sheets_a4_a3' => number_format((float) $requirement->required_quantity, 0),
                    'sheets_a1' => '—',
                ];
            })
            ->values()
            ->all();

        if ($requirements !== []) {
            return $requirements;
        }

        return array_fill(0, 4, [
            'paper_type' => '',
            'sheets_a4_a3' => '',
            'sheets_a1' => '',
        ]);
    }

    protected function pagesPerPadLabel(?ProductionSpecification $spec): string
    {
        if (! $spec) {
            return '—';
        }

        if ($spec->estimated_sheets !== null && $spec->ups) {
            return (string) $spec->estimated_sheets;
        }

        return $spec->production_notes ?: '—';
    }
}
