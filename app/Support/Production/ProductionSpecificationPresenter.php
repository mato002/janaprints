<?php

namespace App\Support\Production;

use App\Models\Production\ProductionSpecification;

class ProductionSpecificationPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(ProductionSpecification $spec): array
    {
        return [
            'id' => $spec->id,
            'has_specification' => true,
            'approval_status' => $spec->approval_status?->value,
            'approval_status_label' => $spec->approval_status?->label(),
            'approval_status_variant' => $spec->approval_status?->badgeVariant(),
            'sections' => [
                'product' => $this->productSection($spec),
                'dimensions' => $this->dimensionsSection($spec),
                'materials' => $this->materialsSection($spec),
                'print' => $this->printSection($spec),
                'finishing' => $this->finishingSection($spec),
                'imposition' => $this->impositionSection($spec),
                'artwork' => $this->artworkSection($spec),
                'notes' => $this->notesSection($spec),
            ],
            'links' => [
                'sales_order_id' => $spec->sales_order_id,
                'sales_order_item_id' => $spec->sales_order_item_id,
                'production_job_card_id' => $spec->production_job_card_id,
                'customer_id' => $spec->customer_id,
            ],
            'updated_at' => $spec->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentSummary(ProductionSpecification $spec): array
    {
        return [
            'has_specification' => true,
            'production_type' => $spec->production_type?->value,
            'production_type_label' => $spec->production_type?->value
                ? str_replace('_', ' ', ucfirst($spec->production_type->value))
                : null,
            'product_description' => $spec->product_description,
            'quantity' => $spec->quantity !== null ? (float) $spec->quantity : null,
            'unit' => $spec->unit,
            'size' => $spec->size,
            'paper' => $spec->paperInventoryItem?->item_name,
            'ink' => $spec->ink_type?->label() ?? $spec->colour_mode,
            'binding' => $spec->binding_type,
            'finishing' => $spec->finishing_type,
            'ups' => $spec->ups,
            'estimated_sheets' => $spec->estimated_sheets,
            'approval_status_label' => $spec->approval_status?->label(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function emptyState(): array
    {
        return [
            'has_specification' => false,
            'message' => __('No structured production specification yet.'),
            'sections' => [],
        ];
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function productSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Production type') => $spec->production_type?->value
                ? str_replace('_', ' ', ucfirst($spec->production_type->value))
                : null,
            __('Description') => $spec->product_description,
            __('Quantity') => $spec->quantity !== null ? number_format((float) $spec->quantity, 0) : null,
            __('Unit') => $spec->unit,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function dimensionsSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Size') => $spec->size,
            __('Finished size') => $spec->finished_size,
            __('Sheet size') => $spec->sheet_size,
            __('Orientation') => $spec->orientation,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function materialsSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Paper') => $spec->paperInventoryItem?->item_name,
            __('Material') => $spec->materialInventoryItem?->item_name,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function printSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Ink type') => $spec->ink_type?->label(),
            __('Ink profile') => $spec->inkProfile?->name,
            __('Colour mode') => $spec->colour_mode,
            __('Sides') => $spec->sides,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function finishingSection(ProductionSpecification $spec): array
    {
        $options = collect([
            'lamination' => __('Lamination'),
            'foiling' => __('Foiling'),
            'spot_uv' => __('Spot UV'),
            'embossing' => __('Embossing'),
            'debossing' => __('Debossing'),
            'die_cutting' => __('Die cutting'),
            'creasing' => __('Creasing'),
            'perforation' => __('Perforation'),
            'numbering_required' => __('Numbering'),
            'eyelets' => __('Eyelets'),
        ])->filter(fn ($label, $key) => (bool) $spec->{$key})->values()->all();

        return $this->fields([
            __('Binding') => $spec->binding_type,
            __('Finishing type') => $spec->finishing_type,
            __('Finishing options') => $options !== [] ? implode(', ', $options) : null,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function impositionSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Ups') => $spec->ups,
            __('Estimated sheets') => $spec->estimated_sheets,
            __('Waste allowance') => $spec->waste_allowance_percent !== null
                ? number_format((float) $spec->waste_allowance_percent, 1).'%'
                : null,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function artworkSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Artwork reference') => $spec->artwork_reference,
            __('Artwork version') => $spec->artwork_version,
        ]);
    }

    /**
     * @return list<array{label: string, value: mixed}>
     */
    protected function notesSection(ProductionSpecification $spec): array
    {
        return $this->fields([
            __('Production notes') => $spec->production_notes,
            __('Delivery notes') => $spec->delivery_notes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $pairs
     * @return list<array{label: string, value: mixed}>
     */
    protected function fields(array $pairs): array
    {
        return collect($pairs)
            ->map(fn ($value, $label) => ['label' => $label, 'value' => $value])
            ->values()
            ->all();
    }
}
